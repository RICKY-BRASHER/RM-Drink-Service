<?php

namespace App\Http\Controllers;
use App\Models\client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use App\Models\produit;
class ActionAdd extends Controller
{
    public function addclient(Request $request){
        $validator = Validator::make($request->all(), [
            'nom_c' => 'required',
            'adresse_c' => 'required',
            'tel_c' => 'required|numeric|digits_between:9,12',
        ], [
            'required' => 'Remplissez tous les champs',
            'tel_c.numeric' => 'Numéro de téléphone invalide',
            'tel_c.digits_between' => 'Le numéro de téléphone doit être entre 9 et 12 chiffres',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ]);
        }
        $verif_id = client::where('id_c',$request->ref_c)->first();
        $verif = client::where('nom_c', $request->nom_c)->where('id_u',Session::get('U_id'))->exists();
        if($verif_id){
            $verif_id->nom_c = $request->input('nom_c');
            $verif_id->adresse_c = $request->input('adresse_c');
            $verif_id->tel_c = $request->input('tel_c');
            $verif_id->save();  
            Session::flash('success', 'Client modifié avec succès');
            return response()->json([
                'success' => true,
                'client' => $verif_id,
            ]);
        }else if($verif){
                return response()->json(['errors' => 'Client Existant Déjà']);
        }else{
            $client = new client();
            $client->id_c = $request->ref_c;
            $client->nom_c = $request->input('nom_c');
            $client->adresse_c = $request->input('adresse_c');
            $client->tel_c = $request->input('tel_c');
            $client->id_u = session('U_id'); // Assurez-vous que l'ID de l'utilisateur est stocké dans la session
            $client->save();
            Session::flash('success', 'Client ajouté avec succès');
            return response()->json([
                'success' => true,
                'client' => $client,
            ]);
        }
        
        
    }
    public function deleteclient($id){
        $client = client::where('id_c', $id)->first();
        if ($client) {
            $client->delete();
            return response()->json(['success' => true]);
        } else {
            return response()->json(['error' => 'Client non trouvé'], 404);
        }
    }
    public function getclient($id){
        $client = client::where('id_c', $id)->first();
        if ($client) {
            return response()->json(['success' => true, 'client' => $client]);
        } else {
            return response()->json(['error' => 'Client non trouvé'], 404);
        }
    }
    public function addproduit(Request $request){
        // Validation des données d'entrée
        $validator = Validator::make($request->all(), [
            'nom_p' => 'required',
            'prix_p' => 'required|numeric',
            'stock_p' => 'required|numeric',
            'fournisseur' => 'required|exists:fournisseurs,ref_f',
        ], [
            'required' => 'Remplissez tous les champs',
            'prix_p.numeric' => 'Le prix doit être un nombre valide',
            'stock_p.numeric' => 'La quantité doit être un nombre',
            'fournisseur.exists' => 'Le fournisseur sélectionné est invalide',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ]);
        }
        $verif_nom = produit::where('nom_p', $request->nom_p)->where('id_u',Session::get('U_id'))->exists();
        $verif_id = produit::where('id_p',$request->ref)->first();
         if($verif_id){
            $verif_id->nom_p = $request->input('nom_p');
            $verif_id->prix_p = $request->input('prix_p');
            $verif_id->categorie = $request->input('categorie');
            $verif_id->qte_p = $request->input('stock_p');
            $verif_id->ref_f = $request->input('fournisseur');
            $verif_id->save();  
            Session::flash('success', 'Produit modifié avec succès');
            return response()->json([
                'success' => true,
                'produit' => $verif_id,
            ]);
        }else if($verif_nom){
            return response()->json(['errors' => 'Produit Existant Déjà']);
        }else{
                $produit = new produit();
                $produit->nom_p = $request->input('nom_p');
                $produit->prix_p = $request->input('prix_p');
                $produit->categorie = $request->input('categorie');
                $produit->qte_p = $request->input('stock_p');
                $produit->ref_f = $request->input('fournisseur');
                $produit->id_u = session('U_id'); // Assurez-vous que l'ID de l'utilisateur est stocké dans la session
                $produit->save();
                Session::flash('success', 'Produit ajouté avec succès');
                return response()->json([
                    'success' => true,
                    'produit' => $produit,
                ]);
        }
        // Logique pour ajouter le produit à la base de données

    }
    public function deleteproduit($id){
        $produit = produit::where('id_p', $id)->first();
        if ($produit) {
            $produit->delete();
            return response()->json(['success' => true]);
        } else {
            return response()->json(['error' => 'Produit non trouvé'], 404);
        }
    }
     public function getproduit($id){
        $produit = produit::where('id_p', $id)->first();
        if ($produit) {
            return response()->json(['success' => true, 'produit' => $produit]);
        } else {
            return response()->json(['error' => 'Produit non trouvé'], 404);
        }
    }
}