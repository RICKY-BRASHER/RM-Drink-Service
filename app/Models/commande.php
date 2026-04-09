<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class commande extends Model
{
    protected $table = 'commandes';
    protected $primaryKey = 'id_v';
    protected $fillable = ['descrip_v', 'prix_total', 'type_paie', 'etat', 'date_v', 'id_c', 'id_u'];

    public function client()
    {
        return $this->belongsTo(client::class, 'id_c', 'id_c');
    }
    public function utilisateur()
    {
        return $this->belongsTo(utilisateur::class, 'id_u', 'id_u');
    }
}
