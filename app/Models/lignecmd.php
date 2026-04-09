<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class lignecmd extends Model
{
    protected $table = 'lignecmds';
    protected $primaryKey = 'id_l';
    protected $fillable = ['qte_l', 'prix_l', 'id_v', 'id_p', 'id_u'];
    protected $casts = [
        'qte_l' => 'integer',
        'prix_l' => 'decimal:2',
    ];
    public function commande()
    {
        return $this->belongsTo(commande::class, 'id_v', 'id_v');
    }
    public function produit()
    {
        return $this->belongsTo(produit::class, 'id_p', 'id_p');
    }
     public function utilisateur()
    {
        return $this->belongsTo(utilisateur::class, 'id_u', 'id_u');
    }
}
