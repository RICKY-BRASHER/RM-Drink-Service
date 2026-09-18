<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promotion;
use App\Models\Produit;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    /**
     * Affiche l'interface de gestion des promotions
     */
    public function index()
    {
        $promotions = Promotion::with(['produit', 'depot'])->orderBy('created_at', 'desc')->get();
        //recuperons les depot et les produit pour chaque depot
        $ProduitsParDepot = Produit::with(['depot'])->get()->groupBy('depot_id');
        $depots = DB::table('depots')->get();
        return view('layout.admin.Promotion', compact('promotions', 'ProduitsParDepot', 'depots'));
    }

    /**
 * ⚡ TOGGLE : Active ou désactive instantanément une campagne
 */
    public function toggleStatut($id)
    {
        try {
            $promo = Promotion::findOrFail($id);
            $promo->est_active = !$promo->est_active; // Inverse la valeur (true/false)
            $promo->save();

            $statutTexte = $promo->est_active ? 'activée' : 'désactivée';
            return response()->json(['success' => true, 'message' => "La promotion a été {$statutTexte}."]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }



    /**
     * Enregistre une nouvelle campagne promotionnelle configurée par l'admin
     */
    public function store(Request $request)
    {
        $request->validate([
            'depot_id' => 'required|exists:depots,id',
            'type' => 'required|in:quantite_offerte,reduction_prix',
            'cible' => 'required|in:produit_unique,marque_globale',
            'produit_id' => 'required_if:cible,produit_unique|nullable|exists:produits,id_produit',
            'marque' => 'required_if:cible,marque_globale|nullable|string|max:100',
            'seuil' => 'required|integer|min:1',
            'offert' => 'required_if:type,quantite_offerte|nullable|integer|min:1',
            'prix_promo' => 'required_if:type,reduction_prix|nullable|numeric|min:0',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
        ]);

        try {
            Promotion::create([
                'type' => $request->input('type'),
                // Si la cible est globale, le produit_id reste null, et vice-versa
                'produit_id' => $request->input('cible') === 'produit_unique' ? $request->input('produit_id') : null,
                'marque' => $request->input('cible') === 'marque_globale' ? $request->input('marque') : null,
                'seuil' => $request->input('seuil'),
                'offert' => $request->input('type') === 'quantite_offerte' ? $request->input('offert') : null,
                'prix_promo' => $request->input('type') === 'reduction_prix' ? $request->input('prix_promo') : null,
                'date_debut' => $request->input('date_debut'),
                'date_fin' => $request->input('date_fin'),
                'depot_id' => $request->input('depot_id'),
            ]);

            return response()->json(['success' => true, 'message' => 'Campagne promotionnelle activée avec succès !']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur système : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Supprime définitivement une promotion du registre
     */
    public function destroy($id)
    {
        try {
            $promo = Promotion::findOrFail($id);
            $promo->delete();
            return response()->json(['success' => true, 'message' => 'La promotion a été retirée du catalogue.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors de la suppression : ' . $e->getMessage()], 500);
        }
    }
}
