<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class utilisateurs extends Authenticatable
{
    use notifiable;
    use HasFactory;
    protected $primaryKey = 'id_u';
    protected $fillable =[
        'nom',
        'email',
        'localisation',
        'contact_u',
        'mdp', 
    ];
    protected $hidden = [
        'password','remember_token',
    ];
    public function setPasswordAttribute($value){
        $this->attributes['mdp'] = Hash::make($value);
    }
}
