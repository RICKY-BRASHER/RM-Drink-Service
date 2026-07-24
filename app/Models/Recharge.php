<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Recharge extends Model
{
    use HasFactory;

    // 1. Nom exact de la table en Base de Données
    protected $table = 'recharges';

    // 2. Les champs autorisés à être remplis en masse
    protected $fillable = [
        'client_id',
        'employe_id',
        'montant_r',
        'solde_apres_r',
        'moyen_recharge',
        'type_mouvement',
        'vente_id'
    ];

    /**
     * 💡 RELATION 1 : Récupérer les informations du client rechargé
     * Utilisation : $recharge->client->nom_u
     */
    public function client()
    {
        // (Modèle cible, clé étrangère locale, clé primaire cible)
        return $this->belongsTo(User::class, 'client_id', 'id_u');
    }

    /**
     * 💡 RELATION 2 : Récupérer l'employé/admin qui a fait la manipulation de caisse
     * Utilisation : $recharge->employe->nom_u
     */
    public function employe()
    {
        return $this->belongsTo(User::class, 'employe_id', 'id_u');
    }
    public function vente()
    {
        return $this->belongsTo(Vente::class, 'vente_id', 'id');
    }
}
