<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Produit extends Model
{
    use HasFactory;
    protected $table = "produits"; // Nom de la table dans la base de données
    protected $primaryKey = "id_produit"; // Clé primaire de la table

    protected $fillable = [
        'nom_p',
        'format_p',
        'prix_achat_p',
        'prix_p',
        'quantite_p',
        'id_fournisseur',
        'depot_id',
        'seuil',
    ];
    public $timestamps = true; // Si vous avez des colonnes created_at et updated_at dans votre table
    public function produit()
    {
        return $this->hasMany(Produit::class, 'produit_id', 'id_produit');
    }
    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class, 'id_fournisseur', 'id_fournisseur');
    }
    public function depot()
    {
        return $this->belongsTo(Depot::class, 'depot_id', 'id');
    }

    /**
     * Les approvisionnements qui contiennent ce produit.
     */
    public function approvisionnements(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Approvisionnement::class, 'details_approvisionnements', 'produit_id', 'approvisionnement_id')
                    ->withPivot('id_det_a', 'quantite_ajoutee')
                    ->withTimestamps();
    }
    public function details_approvisionnements()
    {
        return $this->hasMany(Details_approvisionnement::class, 'produit_id', 'id_produit');
    }

    public function detailVentes()
    {
        return $this->hasMany(DetailVente::class, 'produit_id', 'id_produit');
    }


}
