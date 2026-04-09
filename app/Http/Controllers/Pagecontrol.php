<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\utilisateurs;
use App\Models\client;
use App\Models\produit;
use App\Models\vente;
use App\Models\historique;
use App\Models\stock;
use App\Models\fournisseur;
class Pagecontrol extends Controller
{
    
    public function dashboard()
    {
        $info_u = utilisateurs::find(Session::get('U_id'));
        return view('dashboard', compact('info_u'));
    }
    public function stock()
    {
        $info_u = utilisateurs::find(Session::get('U_id'));
        return view('stock', compact('info_u'));
    }
    public function client()
    {
        $client = client::all()->where('id_u', Session::get('U_id'));
        $info_u = utilisateurs::find(Session::get('U_id'));
        return view('client', compact('info_u', 'client'));
    }
    public function vente()
    {
        $client = client::all()->where('id_u', Session::get('U_id'));
        $produit = produit::all()->where('id_u', Session::get('U_id'));
        $info_u = utilisateurs::find(Session::get('U_id'));
        return view('vente', compact('info_u', 'client', 'produit'));
    }
    public function historique()
    {
        $info_u = utilisateurs::find(Session::get('U_id'));
        return view('historique', compact('info_u'));
    }
    public function produit()
    {
        $fournisseur = fournisseur::all();
        $produit = produit::with('fournisseur')->where('id_u', Session::get('U_id'))->get();
        $info_u = utilisateurs::find(Session::get('U_id'));
        return view('produit', compact('info_u', 'fournisseur', 'produit'));
    }
    
}
