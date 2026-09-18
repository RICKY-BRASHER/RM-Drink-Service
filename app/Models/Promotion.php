<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $table = 'promotions';
    protected $primaryKey = 'id_promo';

    protected $fillable = [
        'depot_id',
        'type',
        'produit_id',
        'marque',
        'seuil',
        'offert',
        'prix_promo',
        'est_active',
        'date_debut',
        'date_fin'
    ];

    public function produit()
    {
        return $this->belongsTo(Produit::class, 'produit_id', 'id_produit');
    }
    public function depot()
{
    return $this->belongsTo(Depot::class, 'depot_id');
}
}
