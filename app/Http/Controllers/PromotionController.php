<?php

namespace App\Http\Controllers;

use App\Models\DetailVente;
use App\Models\Depot;
use App\Models\Produit;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PromotionController extends Controller
{
    public function index()
    {
        $this->ensureAdmin();

        $promotions = Promotion::with(['produit', 'produits', 'depot'])
            ->latest()
            ->get();
        $distributedGifts = DetailVente::query()
            ->join('ventes', 'detail_ventes.vente_id', '=', 'ventes.id')
            ->whereIn('detail_ventes.promotion_id', $promotions->pluck('id_promo'))
            ->where('detail_ventes.est_offert', true)
            ->whereIn('ventes.statut', ['attente', 'payer', 'livrer'])
            ->groupBy('detail_ventes.promotion_id')
            ->selectRaw('detail_ventes.promotion_id, SUM(detail_ventes.quantite) as total')
            ->pluck('total', 'detail_ventes.promotion_id');

        foreach ($promotions as $promotion) {
            $products = $this->eligibleProducts($promotion);
            $stock = (int) floor($products->sum('quantite_p'));

            $promotion->setAttribute('stock_eligible', $stock);
            $promotion->setAttribute(
                'cadeaux_estimables',
                $promotion->type === 'quantite_offerte'
                    && (int) $promotion->seuil + (int) $promotion->offert > 0
                    ? intdiv($stock, (int) $promotion->seuil + (int) $promotion->offert) * (int) $promotion->offert
                    : 0
            );
            $promotion->setAttribute(
                'cadeaux_distribues',
                $distributedGifts->get($promotion->id_promo, 0)
            );
        }

        $ProduitsParDepot = Produit::with('depot')
            ->orderBy('nom_p')
            ->get()
            ->groupBy('depot_id');
        $depots = Depot::orderBy('nom_depot')->get();

        return view('layout.admin.Promotion', compact('promotions', 'ProduitsParDepot', 'depots'));
    }

    public function toggleStatut($id)
    {
        $this->ensureAdmin();

        $promotion = Promotion::findOrFail($id);
        $newStatus = !$promotion->est_active;

        if ($newStatus && in_array($promotion->type, ['quantite_offerte', 'reduction_prix'], true)) {
            $this->ensureNoOverlappingPromotion($promotion, $this->eligibleProducts($promotion)->modelKeys());
        }

        $promotion->est_active = $newStatus;
        $promotion->save();

        $statusText = $promotion->est_active ? 'activée' : 'désactivée';

        return response()->json([
            'success' => true,
            'message' => "La promotion a été {$statusText}.",
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'depot_id' => 'required|integer|exists:depots,id',
            'type' => 'required|in:quantite_offerte,reduction_prix',
            'produits' => 'required|array|min:1',
            'produits.*' => 'required|integer|distinct|exists:produits,id_produit',
            'seuil' => 'required|integer|min:1',
            'offert' => 'required_if:type,quantite_offerte|nullable|integer|min:1',
            'prix_promo' => 'required_if:type,reduction_prix|nullable|numeric|min:0',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
        ]);

        DB::transaction(function () use ($data) {
            Depot::query()->lockForUpdate()->findOrFail($data['depot_id']);
            $products = Produit::query()
                ->where('depot_id', $data['depot_id'])
                ->whereIn('id_produit', $data['produits'])
                ->lockForUpdate()
                ->get();

            if ($products->count() !== count($data['produits'])) {
                throw ValidationException::withMessages([
                    'produits' => 'Tous les produits sélectionnés doivent appartenir au dépôt choisi.',
                ]);
            }

            $referencePrice = round((float) $products->first()->prix_p, 2);
            if ($products->contains(fn (Produit $product) => round((float) $product->prix_p, 2) !== $referencePrice)) {
                throw ValidationException::withMessages([
                    'produits' => 'Les produits d’une même catégorie promotionnelle doivent avoir le même prix de vente.',
                ]);
            }

            if ($data['type'] === 'reduction_prix'
                && (float) $data['prix_promo'] >= $referencePrice) {
                throw ValidationException::withMessages([
                    'prix_promo' => 'Le prix promotionnel doit être inférieur au prix de vente actuel.',
                ]);
            }

            $this->ensureNoOverlappingPromotion(
                null,
                $products->modelKeys(),
                (int) $data['depot_id'],
                $data['date_debut'],
                $data['date_fin']
            );

            $promotion = Promotion::create([
                'depot_id' => $data['depot_id'],
                'type' => $data['type'],
                'seuil' => $data['seuil'],
                'offert' => $data['type'] === 'quantite_offerte' ? $data['offert'] : null,
                'prix_promo' => $data['type'] === 'reduction_prix' ? $data['prix_promo'] : null,
                'prix_reference' => $referencePrice,
                'date_debut' => $data['date_debut'],
                'date_fin' => $data['date_fin'],
                'est_active' => true,
            ]);
            $promotion->produits()->sync($products->modelKeys());
        });

        return response()->json([
            'success' => true,
            'message' => $data['type'] === 'quantite_offerte'
                ? 'Offre créée. Les cadeaux seront calculés automatiquement à la caisse.'
                : 'Remise créée. Le prix réduit s’appliquera à toutes les unités dès que le seuil cumulatif sera atteint.',
        ]);
    }

    public function destroy($id)
    {
        $this->ensureAdmin();

        $promotion = Promotion::findOrFail($id);

        if (DetailVente::where('promotion_id', $promotion->id_promo)->exists()) {
            throw ValidationException::withMessages([
                'promotion' => 'Cette offre apparaît déjà sur des factures. Désactivez-la pour préserver l’historique des ventes.',
            ]);
        }

        $promotion->delete();

        return response()->json([
            'success' => true,
            'message' => 'La promotion a été retirée du catalogue.',
        ]);
    }

    private function ensureAdmin(): void
    {
        abort_unless(Auth::check() && Auth::user()->role_u === 'admin', 403);
    }

    private function eligibleProducts(Promotion $promotion)
    {
        $products = $promotion->produits;

        if ($products->isNotEmpty()) {
            return $products;
        }

        if ($promotion->produit_id) {
            return Produit::where('id_produit', $promotion->produit_id)->get();
        }

        if ($promotion->marque) {
            return Produit::where('nom_p', 'like', '%' . $promotion->marque . '%')->get();
        }

        return collect();
    }

    private function ensureNoOverlappingPromotion(
        ?Promotion $promotion,
        array $productIds,
        ?int $depotId = null,
        ?string $dateStart = null,
        ?string $dateEnd = null
    ): void {
        $promotion ??= new Promotion([
            'depot_id' => $depotId,
            'date_debut' => $dateStart,
            'date_fin' => $dateEnd,
        ]);

        $depotId ??= $promotion->depot_id;
        $dateStart ??= $promotion->date_debut;
        $dateEnd ??= $promotion->date_fin;

        $overlap = Promotion::query()
            ->whereIn('type', ['quantite_offerte', 'reduction_prix'])
            ->where('est_active', true)
            ->whereDate('date_debut', '<=', $dateEnd)
            ->whereDate('date_fin', '>=', $dateStart)
            ->where(function ($query) use ($depotId) {
                $query->where('depot_id', $depotId)->orWhereNull('depot_id');
            })
            ->when($promotion->exists, fn ($query) => $query->where('id_promo', '!=', $promotion->id_promo))
            ->where(function ($query) use ($productIds) {
                $query->whereIn('produit_id', $productIds)
                    ->orWhereHas('produits', fn ($products) => $products->whereIn('produits.id_produit', $productIds));
            })
            ->exists();

        if (!$overlap) {
            $productNames = Produit::whereIn('id_produit', $productIds)->pluck('nom_p');
            $legacyBrands = Promotion::query()
                ->whereIn('type', ['quantite_offerte', 'reduction_prix'])
                ->where('est_active', true)
                ->whereNotNull('marque')
                ->whereDate('date_debut', '<=', $dateEnd)
                ->whereDate('date_fin', '>=', $dateStart)
                ->where(function ($query) use ($depotId) {
                    $query->where('depot_id', $depotId)->orWhereNull('depot_id');
                })
                ->when($promotion->exists, fn ($query) => $query->where('id_promo', '!=', $promotion->id_promo))
                ->pluck('marque');

            $overlap = $legacyBrands->contains(
                fn (string $brand) => $productNames->contains(
                    fn (string $name) => stripos($name, $brand) !== false
                )
            );
        }

        if ($overlap) {
            throw ValidationException::withMessages([
                'produits' => 'Un produit choisi possède déjà une offre active sur tout ou partie de cette période.',
            ]);
        }
    }
}
