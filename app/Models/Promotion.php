<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Promotion extends Model
{
    protected $table = 'promotions';
    protected $primaryKey = 'id_promo';

    protected $casts = [
        'est_active' => 'boolean',
    ];

    protected $fillable = [
        'depot_id',
        'type',
        'produit_id',
        'marque',
        'seuil',
        'offert',
        'prix_promo',
        'prix_reference',
        'est_active',
        'date_debut',
        'date_fin'
    ];

    public function produit()
    {
        return $this->belongsTo(Produit::class, 'produit_id', 'id_produit');
    }

    public function produits(): BelongsToMany
    {
        return $this->belongsToMany(
            Produit::class,
            'promotion_produit',
            'promotion_id',
            'produit_id',
            'id_promo',
            'id_produit'
        );
    }

    public function depot()
    {
        return $this->belongsTo(Depot::class, 'depot_id');
    }
}
