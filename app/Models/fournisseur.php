<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class fournisseur extends Model
{
    protected $table = 'fournisseurs';
    protected $primaryKey = 'ref_f';
    protected $fillable = ['ref_f','nom_f'];

}
