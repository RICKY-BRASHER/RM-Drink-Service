<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vente extends Model
{
    use HasFactory;

    protected $table = 'ventes';

    protected $fillable = [
        'client_id',
        'code_facture',
        'montant_total',
        'montant_verse',
        'mode_paiement',
        'statut',
        'employe_id'
    ];
    public function recharge()
    {
        return $this->hasOne(Recharge::class, 'vente_id', 'id');
    }
    // Relation : une vente appartient à un client
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id', 'id_u');
    }

    // Relation : une vente a plusieurs détails
    public function details()
    {
        return $this->hasMany(DetailVente::class, 'vente_id');
    }
    public function detailVentes()
    {
        // Spécifiez : (Modèle cible, clé étrangère dans la table cible, clé primaire locale)
        return $this->hasMany(DetailVente::class, 'vente_id', 'id');
    }

    /**
     * Relation optionnelle pour récupérer l'employé/caissier titulaire
     */
    public function employe()
    {
        return $this->belongsTo(User::class, 'employe_id', 'id_u');
    }
}

