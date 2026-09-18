<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class Authentification extends Controller
{
   /* public function register_client(Request $request)
    {
        // 1. NORMALISATION DU NUMÉRO (Approche Facebook)
        if ($request->has('tel_u')) {
            // Supprime tous les espaces, tirets, points et parenthèses
            $tel = preg_replace('/[\s\-\.\(\)]/', '', $request->input('tel_u'));

            // CAS A : L'utilisateur a saisi 9 chiffres (ex: 690408038)
            if (strlen($tel) === 9 && is_numeric($tel) && str_starts_with($tel, '6')) {
                $tel = '237' . $tel; // Normalisation internationale
            } 
            // CAS B : L'utilisateur a saisi 12 chiffres (ex: 237690408038)
            // On vérifie s'il commence par 237 et que le 4ème chiffre est bien un 6
            elseif (strlen($tel) === 12 && is_numeric($tel) && str_starts_with($tel, '237') && $tel[3] === '6') {
                // Normalisation internationale
            } 
            // CAS TRICHE / ERREUR : Le format ne respecte rien du tout
            else {
                return response()->json([
                    'success' => false,
                    'errors' => ['tel_u' => ['Le numéro doit faire 9 chiffres commençant par 6, ou 12 chiffres commençant par 237.']]
                ], 422);
            }

            // On réinjecte le téléphone propre et validé dans la requête
            $request->merge(['tel_u' => $tel]);
        }

        // 2. VALIDATION DES DONNÉES
        $validator = Validator::make($request->all(), [
            'nom_u' => 'required|string|max:255',
            'localisation_u' => 'required|string|max:255',
            'email_u' => 'required|string|email|max:255|unique:users,email_u',
            'tel_u' => ['required', 'string', 'unique:users,tel_u', 'regex:/^2376[0-9]{8}$/'],

            'mdp_u' => 'required|string|min:8',
            'confirmer_mdp_u' => 'required|string|same:mdp_u',
            'depot_id' => 'nullable|exists:depots,id', // Validation du dépôt si fourni
        ],
        [
            'nom_u.required' => 'Le nom est requis.',
            'localisation_u.required' => 'La localisation est requise.',
            'email_u.required' => 'L\'adresse email est requise.',
            'email_u.email' => 'L\'adresse email doit être valide.',
            'tel_u.required' => 'Le numéro de téléphone est requis.',
            'tel_u.unique' => 'Ce numéro de téléphone est déjà utilisé par un autre client.',
            'tel_u.regex' => 'Le format du numéro de téléphone n\'est pas valide (Ex: +2376xxxxxx).',
            'email_u.unique' => 'Cette adresse email est déjà associée à un compte.',
            'mdp_u.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'confirmer_mdp_u.required' => 'La confirmation du mot de passe est requise.',
            'confirmer_mdp_u.same' => 'La confirmation du mot de passe doit correspondre au mot de passe.',
        ]);

        // CHANGEMENT AJAX : Si la validation échoue, on renvoie les erreurs en JSON (Code HTTP 422)
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // 3. ENREGISTREMENT SÉCURISÉ EN BASE DE DONNÉES
        $user = new User();
        $user->nom_u = $user->nom_u = $request->input('nom_u');
        $user->localisation_u = $request->input('localisation_u');
        $user->email_u = $request->input('email_u'); 
        $user->tel_u = $request->input('tel_u');     
        $user->mdp_u = Hash::make($request->input('mdp_u'));
        $user->role_u = 'client';
        $user->type_u = $request->input('type_u', 'particulier'); 
        $user->depot_id = $request->input('depot_id'); // Assigner l'ID du dépôt
        $user->solde_u = 0.00;
        $user->save();

        // Connecter automatiquement le client après la création de son compte
        //Auth::login($user);
        session()->flash('success', 'Votre compte a été créé. Vous pouvez désormais vous connecter');
        // CHANGEMENT AJAX : En cas de succès, on renvoie un signal positif et l'URL de redirection (Code HTTP 200)
        return response()->json([
            'success' => true,
            'redirect' => route('login.client') // Tu peux remplacer par 'client.dashboard' si tu veux le connecter directement
        ], 200);
    }
    public function login_client(Request $request)
    {
        // 1. Validation de la saisie brute
        $validator = Validator::make($request->all(), [
            'email_or_phone' => 'required|string',
            'mdp_connect' => 'required|string',
            'remember_me' => 'nullable|boolean', // Validation du champ optionnel
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 2. Détermination du type d'identifiant (Email ou Téléphone)
        $loginValue = $request->input('email_or_phone');
        $field = filter_var($loginValue, FILTER_VALIDATE_EMAIL) ? 'email_u' : 'tel_u';//  

        if ($field === 'tel_u') {
            $loginValue = preg_replace('/[\s\-\.\(\)]/', '', $loginValue);// Nettoyage du numéro de téléphone
            if (strlen($loginValue) === 9 && is_numeric($loginValue)) { // Si c'est un numéro local de 9 chiffres, on le standardise
                $loginValue = '237' . $loginValue; // Standardisation Facebook
            }
        }

        // 3. Récupération de l'utilisateur
        $user = User::where($field, $loginValue)->first();

        // Vérification du mot de passe (via ta colonne personnalisée mdp_u)
        if (!$user || !Hash::check($request->mdp_connect, $user->mdp_u)) {
            return response()->json([
                'errors' => ['email_or_phone' => ['Identifiants de connexion incorrects.']]
            ], 422);
        }

        // 4. GESTION DU REMEMBER ME
        // Si la case est cochée, $remember vaudra true, sinon false
        $remember = $request->has('remember_me') && $request->input('remember_me') == '1';
        // mise a jour de la session pour le remember me
        if ($remember) {
            // On peut stocker l'ID de l'utilisateur dans la session pour une reconnexion automatique
            session(['remembered_user_id' => $user->id]);
        } else {
            // Si l'utilisateur ne veut pas être "remembered", on supprime toute trace précédente
            session()->forget('remembered_user_id');
        }
        // On connecte l'utilisateur en passant la variable $remember en deuxième paramètre
        auth()->login($user, $remember);
        

        return response()->json([
            'success' => true,
            'redirect' => route('client.accueil')
        ], 200);
    }*/
    public function login_pro(Request $request)
    {
        // 1. Validation de la forme de la requête
        $validator = Validator::make($request->all(), [
            'email_or_phone' => 'required|string',
            'password' => 'required|string',
        ], [
            'email_or_phone.required' => 'L\'identifiant professionnel est requis.',
            'password.required' => 'Le code secret est obligatoire.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $loginValue = $request->input('email_or_phone');
        
        // Détermination automatique si l'identifiant est un Email ou un Téléphone normalisé
        $field = filter_var($loginValue, FILTER_VALIDATE_EMAIL) ? 'email_u' : 'tel_u';
        if ($field === 'tel_u') {
            $loginValue = preg_replace('/[\s\-\.\(\)]/', '', $loginValue);
            if (strlen($loginValue) === 9 && is_numeric($loginValue)) {
                $loginValue = '237' . $loginValue;
            }
        }

        // 2. Recherche de l'utilisateur dans la table users
        $user = User::where($field, $loginValue)->first();

        // Vérification de l'existence et de l'intégrité du mot de passe (colonne mdp_u)
        if (!$user || !Hash::check($request->input('password'), $user->mdp_u)) {
            return response()->json([
                'errors' => ['email_or_phone' => ['Identifiants d\'administration incorrects.']]
            ], 422);
        }

        // 3. SÉCURITÉ PAR ROLES ET TYPES (Cahier des Charges)
        if ($user->role_u === 'admin') {
            
            // Cas A : C'est un Administrateur (M. Dimitri / Mme Christine) -> Accès Console Maîtresse
            Auth::login($user, $request->has('remember'));
            
            
            return response()->json([
                'success' => true,
                'redirect' => route('accueil.admin') 
            ], 200);

        } elseif ($user->role_u === 'employe' && $user->type_u === 'personnel') {
            
            // Cas B : C'est un Employé de type Personnel -> Accès Page Secrétariat / Facturation dépôt
            Auth::login($user, $request->has('remember'));
            

            return response()->json([
                'success' => true,
                'redirect' => route('dashboard') // Nom de ta route vers la page de facturation
            ], 200);

        }

        // Cas C : C'est un compte existant mais sans privilège d'accès interne (ex: un client qui triche)
        return response()->json([
            'success' => false,
            'message' => 'Accès refusé : Ce portail est strictement réservé au personnel et à la direction.'
        ], 403); // Code HTTP 403 : Interdit / Refus d'autorisation
    }
    public function logout(Request $request)
    {
        // Déconnexion de l'utilisateur
        Auth::logout();

        // Invalidation de la session
        $request->session()->invalidate();

        // Regénération du token CSRF pour la sécurité
        $request->session()->regenerateToken();

        // Redirection vers la page d'accueil ou de connexion
        return redirect()->route('LandingPage');
    }

}
