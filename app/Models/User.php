<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use App\Models\Depot;
use Illuminate\Support\Str;
class User extends Authenticatable
{
    use Notifiable;
    use HasFactory;
    protected $table = 'users';
    protected $primaryKey = 'id_u';
    public $incrementing = false; // Indique que la clé primaire n'est pas auto-incrémentée
    protected $keyType = 'int'; // Indique que la clé primaire est de type string

    protected $fillable =[
        'nom_u',
        'localisation_u',
        'tel_u',
        'email_u',
        'role_u',
        'type_u',
        'depot_id',
        'solde_u',
        'mdp_u'
    ];
    protected $dates = [
        'created_at',
        'updated_at',
    ];
    //cle etrangere
    public function depot(){
        return $this->belongsTo(Depot::class, 'depot_id');
    }
    protected $hidden = [
        'mdp_u','remember_token',
    ];

    protected static function booted() 
    {
        parent::booted();

        static::creating(function ($model) {
            if (empty($model->id_u)) { 
                do {
                    // mt_rand() génère un nombre aléatoire à 12 chiffres
                    $matricule = mt_rand(100000000000, 999999999999);
                } while (static::where('id_u', $matricule)->exists()); // Sécurité anti-doublon

                $model->id_u = $matricule;
            }
        });
    }
    public function setPasswordAttribute($value){
         // Si le mot de passe est déjà haché (commence par $2y$), on ne le rehache pas
        $this->attributes['mdp_u'] = Str::startsWith($value, '$2y$') ? $value : Hash::make($value); 
    }

    public function approvisionnements(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Approvisionnement::class, 'id_u_admin', 'id_u');
    }

}
