<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class client extends Model
{
    protected $table = 'clients';
    protected $primaryKey = 'id_c';
    protected $keyType = 'string';
    protected $fillable = ['nom_c', 'adresse_c', 'tel_c','solde_c', 'id_u'];

    public function utilisateur()
    {
        return $this->belongsTo(utilisateur::class, 'id_u', 'id_u');
    }
}
