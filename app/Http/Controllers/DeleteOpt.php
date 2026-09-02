<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DeleteOpt extends Controller
{
    /**
 * 🗑️ Supprime définitivement un dépôt du réseau après audit de sécurité
 */
public function detruireDepotPhysique($id)
{
    // 🔒 SÉCURITÉ GÉNERALE : Vérification du grade admin
    if (!auth()->check() || auth()->user()->role_u !== 'admin') {
        return response()->json(['success' => false, 'message' => 'Action non autorisée.'], 403);
    }

    $depot = DB::table('depots')->where('id', $id)->first();
    
    if (!$depot) {
        return response()->json(['success' => false, 'message' => 'Ce dépôt est introuvable ou a déjà été supprimé.'], 404);
    }

    DB::beginTransaction();
    try {
        
        // 🚀 NETTOYAGE DES FLUX : Suppression physique de la ligne dans la table depots
        // Les liaisons "ON DELETE CASCADE" configurées dans tes migrations vont automatiquement 
        // nettoyer les tables dépendantes pour éviter des erreurs de clés orphelines.
        DB::table('depots')->where('id', $id)->delete();

        DB::commit();
        return response()->json([
            'success' => true,
            'message' => "La section '" . strtoupper($depot->nom_depot) . "' a été entièrement retirée du réseau Distrib237."
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => "Erreur système lors du nettoyage : " . $e->getMessage()
        ], 500);
    }
}

public function deleteuser($id)
{
    // Vérification du rôle de l'utilisateur connecté
    if (!auth()->check() || auth()->user()->role_u !== 'admin') {
        return response()->json(['success' => false, 'message' => 'Action non autorisée.'], 403);
    }

    $user = DB::table('users')->where('id_u', $id)->first();
    
    if (!$user) {
        return response()->json(['success' => false, 'message' => 'Utilisateur introuvable ou déjà supprimé.'], 404);
    }

    DB::beginTransaction();
    try {
        // Suppression physique de l'utilisateur
        DB::table('users')->where('id_u', $id)->delete();

        DB::commit();
        return response()->json([
            'success' => true,
            'message' => "L'individu '" . strtoupper($user->nom_u) . "' a été supprimé avec succès."
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => "Erreur système lors de la suppression : " . $e->getMessage()
        ], 500);
    }

}

public function deleteproduit($id)
{
    // Vérification du rôle de l'utilisateur connecté
    if (!auth()->check() || auth()->user()->role_u !== 'admin') {
        return response()->json(['success' => false, 'message' => 'Action non autorisée.'], 403);
    }

    $produit = DB::table('produits')->where('id_produit', $id)->first();
    
    if (!$produit) {
        return response()->json(['success' => false, 'message' => 'Produit introuvable ou déjà supprimé.'], 404);
    }

    DB::beginTransaction();
    try {
        // Suppression physique du produit
        DB::table('produits')->where('id_produit', $id)->delete();

        DB::commit();
        return response()->json([
            'success' => true,
            'message' => "Le produit '" . strtoupper($produit->nom_p) . "' a été supprimé avec succès."
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => "Erreur système lors de la suppression : " . $e->getMessage()
        ], 500);
    }

}


}