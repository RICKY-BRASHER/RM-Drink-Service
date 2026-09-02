<?php

namespace App\Http\Controllers;
use App\Models\NotificationSysteme;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markAsRead(Request $request)
{
    $ids = $request->input('ids', []);

    // 1. Vérification stricte du tableau
    if (!empty($ids) && is_array($ids)) { 
        
        // 2. Exécution et récupération du nombre de lignes affectées
        $lignesModifiees = NotificationSysteme::whereIn('id_notif', $ids)
            ->where('statut_lecture', 'non_lu')
            ->update(['statut_lecture' => 'lu']);

        // 3. Réponse conditionnelle basée sur le résultat réel
        if ($lignesModifiees > 0) {
            return response()->json([
                'status' => 'success',
                'message' => "$lignesModifiees notification(s) mise(s) à jour."
            ], 200);
        }
    }

    // 4. Retour systématique si le tableau est vide ou si rien n'a changé
    return response()->json([
        'status' => 'no_change',
        'message' => 'Aucune notification n’a été modifiée.'
    ], 200); // Vous pouvez utiliser 400 ou 422 si l'absence d'ID est une erreur logicielle

}
}
