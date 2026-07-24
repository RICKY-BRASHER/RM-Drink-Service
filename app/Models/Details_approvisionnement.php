<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Details_approvisionnement extends Model 
{
    // Nom de la table
    protected $table = 'detail_approvisionnements';

    // Clé primaire personnalisée
    protected $primaryKey = 'id_det_a';

    // Champs assignables en masse
    protected $fillable = [
        'approvisionnement_id',
        'produit_id',
        'quantite_ajoutee',
    ];

    /**
     * Relation avec l'approvisionnement parent.
     */
    public function approvisionnement(): BelongsTo
    {
        return $this->belongsTo(Approvisionnement::class, 'approvisionnement_id', 'id_app');
    }

    /**
     * Relation avec le produit concerné.
     */
    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class, 'produit_id', 'id_produit');
    }
}
