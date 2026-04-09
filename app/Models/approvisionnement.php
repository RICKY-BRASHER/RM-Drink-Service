<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class approvisionnement extends Model
{
    protected $table = 'approvisionnements';
    protected $primaryKey = 'id_a';
    protected $fillable = ['qte_a', 'date_a', 'id_p', 'id_u'];

    public function produit()
    {
        return $this->belongsTo(produit::class, 'id_p', 'id_p');
    }

     public function utilisateur()
    {
        return $this->belongsTo(utilisateur::class, 'id_u', 'id_u');
    }  
}
