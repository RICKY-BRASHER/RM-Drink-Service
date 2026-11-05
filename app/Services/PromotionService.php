<?php

namespace App\Services;

use App\Models\DetailVente;
use App\Models\Promotion;
use App\Models\Produit;
use App\Models\Vente;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PromotionService
{
    public function giftQuantityFor(float $purchasedQuantity, int $threshold, int $giftQuantity): int
    {
        if ($threshold < 1 || $giftQuantity < 1) {
            return 0;
        }

        return (int) floor($purchasedQuantity / $threshold) * $giftQuantity;
    }

    public function syncGiftLines(Vente $vente, int $depotId, bool $requireAvailableGift = true): void
    {
        DB::table('detail_ventes')
            ->where('vente_id', $vente->id)
            ->where('est_offert', true)
            ->delete();

        $paidLines = DetailVente::query()
            ->where('vente_id', $vente->id)
            ->where('est_offert', false)
            ->with('produit')
            ->get();

        $paidQuantities = $paidLines->groupBy('produit_id')
            ->map(fn (Collection $lines) => (float) $lines->sum('quantite'));
        $allocatedGiftQuantities = [];

        foreach ($paidLines as $line) {
            $line->prix_unitaire = $line->produit->prix_p;
            $line->total_ligne = (float) $line->quantite * (float) $line->produit->prix_p;
            $line->promotion_id = null;
            $line->save();
        }

        $promotions = Promotion::query()
            ->with('produits')
            ->whereIn('type', ['quantite_offerte', 'reduction_prix'])
            ->where('est_active', true)
            ->whereDate('date_debut', '<=', today())
            ->whereDate('date_fin', '>=', today())
            ->where(function ($query) use ($depotId) {
                $query->where('depot_id', $depotId)->orWhereNull('depot_id');
            })
            ->orderBy('id_promo')
            ->get();

        foreach ($promotions as $promotion) {
            $eligibleProductIds = $this->eligibleProducts($promotion, $depotId)->modelKeys();
            if ($eligibleProductIds === []) {
                continue;
            }

            $eligibleProducts = Produit::query()
                ->whereIn('id_produit', $eligibleProductIds)
                ->where('depot_id', $depotId)
                ->orderBy('id_produit')
                ->lockForUpdate()
                ->get();
            $eligibleIds = $eligibleProducts->modelKeys();
            $purchasedQuantity = $paidLines
                ->whereIn('produit_id', $eligibleIds)
                ->sum('quantite');

            if ($purchasedQuantity < (int) $promotion->seuil) {
                continue;
            }

            if ($promotion->type === 'reduction_prix') {
                $this->applyPricePromotion(
                    $paidLines->whereIn('produit_id', $eligibleIds),
                    $eligibleProducts,
                    $promotion
                );
                continue;
            }

            $giftQuantity = $this->giftQuantityFor(
                (float) $purchasedQuantity,
                (int) $promotion->seuil,
                (int) $promotion->offert
            );

            if ($giftQuantity === 0) {
                continue;
            }

            $giftProduct = $eligibleProducts
                ->filter(function (Produit $product) use ($paidQuantities, $allocatedGiftQuantities, $giftQuantity) {
                    $remaining = (float) $product->quantite_p
                        - (float) $paidQuantities->get($product->id_produit, 0)
                        - (float) ($allocatedGiftQuantities[$product->id_produit] ?? 0);

                    return $remaining >= $giftQuantity;
                })
                ->shuffle()
                ->first();

            if (!$giftProduct) {
                if (!$requireAvailableGift) {
                    continue;
                }

                throw ValidationException::withMessages([
                    'promotion' => "L'offre « {$promotion->id_promo} » est déclenchée, mais aucun produit de sa catégorie n'a assez de stock pour le cadeau. Réapprovisionnez le dépôt ou désactivez l'offre.",
                ]);
            }

            DB::table('detail_ventes')->insert([
                'vente_id' => $vente->id,
                'produit_id' => $giftProduct->id_produit,
                'quantite' => $giftQuantity,
                'prix_unitaire' => 0,
                'total_ligne' => 0,
                'est_offert' => true,
                'promotion_id' => $promotion->id_promo,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $allocatedGiftQuantities[$giftProduct->id_produit] =
                ($allocatedGiftQuantities[$giftProduct->id_produit] ?? 0) + $giftQuantity;
        }

        $vente->montant_total = DB::table('detail_ventes')
            ->where('vente_id', $vente->id)
            ->sum('total_ligne');
        $vente->save();
    }

    private function applyPricePromotion(Collection $lines, Collection $products, Promotion $promotion): void
    {
        $referencePrice = (float) ($promotion->prix_reference
            ?? $products->first()?->prix_p
            ?? 0);
        $promoPrice = (float) $promotion->prix_promo;

        if ($referencePrice <= 0 || $promoPrice < 0 || $promoPrice >= $referencePrice) {
            return;
        }

        foreach ($products as $product) {
            if (round((float) $product->prix_p, 2) !== round($referencePrice, 2)) {
                return;
            }
        }

        foreach ($lines as $line) {
            $line->prix_unitaire = $promoPrice;
            $line->total_ligne = (float) $line->quantite * $promoPrice;
            $line->promotion_id = $promotion->id_promo;
            $line->save();
        }
    }

    private function eligibleProducts(Promotion $promotion, int $depotId): Collection
    {
        $products = $promotion->produits
            ->where('depot_id', $depotId)
            ->values();

        if ($products->isNotEmpty()) {
            return $products;
        }

        if ($promotion->produit_id) {
            return Produit::query()
                ->where('depot_id', $depotId)
                ->where('id_produit', $promotion->produit_id)
                ->get();
        }

        if ($promotion->marque) {
            return Produit::query()
                ->where('depot_id', $depotId)
                ->where('nom_p', 'like', '%' . $promotion->marque . '%')
                ->get();
        }

        return collect();
    }
}
