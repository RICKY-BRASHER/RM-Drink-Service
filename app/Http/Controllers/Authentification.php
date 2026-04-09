<?php

namespace App\Http\Controllers;
use App\Models\utilisateurs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;


class Authentification extends Controller
{
    public function showLoginForm(){
        return view('connexion');
    }

    public function inscrit(Request $request){
        $validator= Validator::make($request->all(),[
            'nom'=>'required',
            'email'=>'required|email',
            'localisation'=>'required',
            'contact_u'=>'required|numeric|digits_between:9,12',
            'mdp'=>'required',
        ],[
            'required' => 'Remplissez tous les champs',
            'email.email'=>'Email Non Valide',
            'contact_u.numeric'=>'Numero invalide',
            'contact_u.digits_between'=>'Numero entre 9 et 12 chiffres',
        ]);
        if($validator->fails()){
            return response()->json([
                'errors' => $validator->messages()->first(),
            ]);
        }
        $nom_ex = utilisateurs::where('nom', $request->nom)->exists();
        $email_ex = utilisateurs::where('email', $request->email)->exists();
        if($nom_ex && $email_ex){
            return response()->json(['errors' => 'Compte Existant Déjà']);
        }elseif($nom_ex){
            return response()->json(['errors' => 'Nom Existe deja']);
        }elseif($email_ex){
            return response()->json(['errors' => 'Email Deja Utiliser']);
        }
        $user = new utilisateurs();
        $user->nom = $request->input('nom');
        $user->email = $request->input('email');
        $user->contact_u = $request->input('contact_u');
        $user->localisation = $request->input('localisation');
        $user->mdp = Hash::make($request->input('mdp'));
        $user->save();
        Session::flash('success','COMPTE CRÉÉ AVEC SUCCÈS !');
        return response()->json([
            'success' => true,
            'redirect' => route('connexion')
        ]);
    }

    public function connecte(Request $request){
        $mail = $request->input('nom');
        $mdp = $request->input('mdp');
        if(!empty($mail) && !empty($mdp)){
            $verif = utilisateurs::where('email',$mail)->first();
            if($verif){
                if(Hash::check($mdp,$verif->mdp)){
                    Session::put('U_id',$verif->id_u);
                    $info = session('U_id');
                    $info_u = utilisateurs::find($info);
                    Session::flash('success','CONNEXION REUSSIE');
                    return response()->json([
                        'success' => true,
                        'redirect' => route('dashboard')
                    ]);
                }else{
                    return response()->json(['errors' => 'Mot de passe non valide']);
                }
            }else{
                return response()->json(['errors' => 'Compte Non Existant']);
            }
        }else{
            return response()->json(['errors' => 'Remplissez tous les champs']);
        }
        
    }
    public function logout(Request $request) {
        // Supprime la variable de session spécifique
        $request->session()->forget('U_id'); 
        Session::flush();
        // Invalide la session complète et régénère le jeton CSRF pour la sécurité
        $request->session()->invalidate();
    
        return redirect()->route('connexion')->with('success', 'Vous êtes déconnecté.');
    }
}