<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Approvisionnement extends Model
{
    // Nom de la table
    protected $table = 'approvisionnements';

    // Clé primaire personnalisée
    protected $primaryKey = 'id_app';

    // Champs assignables en masse
    protected $fillable = [
        'id_u_admin',
        'id_fournisseur',
        'depot_id',
        'total_boissons',
    ];

    /**
     * Relation avec l'administrateur (User) qui a validé.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_u_admin', 'id_u');
    }

    /**
     * Relation avec le fournisseur.
     */
    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class, 'id_fournisseur', 'id_fournisseur');
    }
    /**
     * Les lignes de détails de cet approvisionnement.
     */
    public function details(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DetailApprovisionnement::class, 'approvisionnement_id', 'id_app');
    }

    /**
     * Les produits contenus dans cet approvisionnement.
     */
    public function produits(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Produit::class, 'detail_approvisionnements', 'approvisionnement_id', 'produit_id')
                    ->withPivot('id_det_a', 'quantite_ajoutee')
                    ->withTimestamps();
    }

}
