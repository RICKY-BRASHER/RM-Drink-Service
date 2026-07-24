<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Fournisseur extends Model
{
    protected $table = 'fournisseurs';
    protected $primaryKey = 'id_fournisseur';
    public $timestamps = false;
    protected $fillable = [
        'nom_fournisseur',
    ];
    public function fournisseurs()
    {
        return $this->hasMany(fournisseur::class, 'id_fournisseur');// Relation avec la table "fournisseurs"
    }
    /**
 * Les approvisionnements liés à ce fournisseur.
 */
    public function approvisionnements()
    {
        return $this->hasMany(Approvisionnement::class, 'id_fournisseur', 'id_fournisseur');
    }

}
