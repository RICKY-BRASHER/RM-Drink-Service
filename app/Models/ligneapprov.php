<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ligneapprov extends Model
{
    protected $table = 'ligneapprovs';
    protected $primaryKey = 'id_la';
    protected $fillable = ['qte_la', 'id_a', 'id_p', 'id_u'];
    
     public function utilisateur()
    {
        return $this->belongsTo(utilisateur::class, 'id_u', 'id_u');
    }

    public function approvisionnement()
    {
        return $this->belongsTo(approvisionnement::class, 'id_a', 'id_a');
    }

     public function produit()
    {
        return $this->belongsTo(produit::class, 'id_p', 'id_p');
    }
}
