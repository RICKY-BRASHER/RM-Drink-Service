<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class produit extends Model
{
    protected $table = 'produits';
    protected $primaryKey = 'id_p';
    protected $fillable = ['nom_p','categorie', 'prix_p', 'qte_p', 'ref_f', 'id_u'];

    public function fournisseur()
    {
        return $this->belongsTo(fournisseur::class, 'ref_f', 'ref_f');
    }

     public function utilisateur()
    {
        return $this->belongsTo(utilisateur::class, 'id_u', 'id_u');
    }
}
