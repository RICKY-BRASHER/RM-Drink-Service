<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class alertes extends Model
{
    protected $table = 'alertes_stocks';

    // Clé primaire personnalisée
    protected $primaryKey = 'id_alerte';

    // Champs modifiables en masse
    protected $fillable = [
        'produit_id',
        'depot_id',
        'quantite_restante',
        'seuil_limite',
        'statut_lecture',
    ];

    // Relation avec le Produit
    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class, 'produit_id', 'id_produit');
    }

    // Relation avec le Dépôt
    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class, 'depot_id', 'id');
    }
}
