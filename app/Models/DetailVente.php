<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailVente extends Model
{
    use HasFactory;

    protected $table = 'detail_ventes';

    protected $casts = [
        'est_offert' => 'boolean',
    ];

    protected $fillable = [
        'vente_id',
        'produit_id',
        'quantite',
        'prix_unitaire',
        'total_ligne',
        'est_offert',
        'promotion_id',
    ];

    // Relation : un détail appartient à une vente
    public function vente()
    {
        return $this->belongsTo(Vente::class, 'vente_id');
    }

    // Relation : un détail correspond à un produit
    public function produit()
    {
        return $this->belongsTo(Produit::class, 'produit_id', 'id_produit');
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id', 'id_promo');
    }
}
