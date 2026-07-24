<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Depot extends Model
{
    use HasFactory;

    /**
     * Le nom de la table associée au modèle dans MySQL.
     * Indispensable car ta migration crée la table 'depot' (sans s).
     *
     * @var string
     */
    protected $table = 'depots';

    /**
     * La clé primaire associée à la table.
     * Comme tu as utilisé $table->id(), c'est 'id' par défaut.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Les attributs de ton formulaire et de ta table qui sont modifiables en masse.
     * Cela correspond exactement aux champs de ta migration et de ton formulaire AJAX.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom_depot',
        'adresse_depot',
        'open',
        'close',
        'etat_depot'
    ];

    /**
     * Les attributs qui doivent être convertis dans un type natif.
     * Convertir 'open' et 'close' en type 'time' facilite les manipulations d'heures en PHP.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'open' => 'datetime:H:i',
        'close' => 'datetime:H:i',
        'etat_depot' => 'string'
    ];
    protected $attributes = [
        'etat_depot' => 'operationnel', // Valeur par défaut pour l'état du dépôt
    ];
    
    public function depot()
    {
        return $this->hasMany(Depot::class, 'id'); // Relation avec la table "depots"
    }
    public function user()
    {
        return $this->hasMany(User::class,'depot_id','id');
    }
    public function produit()
    {
        return $this->hasMany(produit::class,'depot_id','id');
    }
    public function employes()
    {
        // Jointure sur la clé étrangère depot_id de votre table users
        return $this->hasMany(User::class, 'depot_id', 'id')
                   ->where('role_u', 'employe'); // Filtre strict
    }
}
