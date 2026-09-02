<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetourCasier extends Model
{
    protected $table = 'retours_casiers';
    protected $primaryKey = 'id_rc';

    protected $fillable = [
        'client_id',
        'employe_id',
        'depot_id',
        'fournisseur_id',
        'vente_id',
        'quantite_rendue'
    ];

    // Les relations pour l'historique
    public function client() { return $this->belongsTo(User::class, 'client_id', 'id_u'); }
    public function employe() { return $this->belongsTo(User::class, 'employe_id', 'id_u'); }
    public function fournisseur() { return $this->belongsTo(Fournisseur::class, 'fournisseur_id', 'id_fournisseur'); }
    public function vente() { return $this->belongsTo(Vente::class, 'vente_id', 'id'); }
}
