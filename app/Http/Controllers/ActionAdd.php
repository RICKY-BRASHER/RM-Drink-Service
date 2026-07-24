<?php

namespace App\Http\Controllers;
use App\Models\Depot;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
class ActionAdd extends Controller
{

    public function addmoddepot(Request $request){
        // 1. SÉCURITÉ DIRECTION : Seul le rôle admin passe ce verrou
        if (!Auth::check() || Auth::user()->role_u !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Accès interdit : Opération réservée à la direction générale.'
            ], 403);
        }

        // 2. VALIDATION STRICTE DU FORMULAIRE (Correspondant à tes attributs name)
        $validator = Validator::make($request->all(), [
            'nom_depot' => 'required|string|max:255',
            'secteur_geographique' => 'required|string|max:255',
            'etat_depot' => 'required|string|in:operationnel,inactif,en_maintenance',
            'heure_ouverture' => 'required|date_format:H:i',
            'heure_verrouillage' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // 3. ANALYSE DU SCÉNARIO (AJOUT OU MODIFICATION SIMULTANÉE)
        if ($request->has('id_d') && !empty($request->input('id_d'))) {
            
            // Mode MODIFICATION : On localise l'ID existant
            $depot = Depot::find($request->input('id_d'));
            if (!$depot) {
                return response()->json(['success' => false, 'message' => 'Dépôt introuvable dans le réseau.'], 404);
            }
            $message = "Les verrous logiques et horaires du " . $request->input('nom_depot') . " ont été mis à jour.";
            
        } else {
            
            // Mode CRÉATION : On crée une nouvelle instance vierge
            $depot = new Depot();
            $message = "Le nouveau dépôt [ " . $request->input('nom_depot') . " ] a été raccordé au réseau commercial.";
            
        }

        // 4. CORRESPONDANCE COMPTABLE ET ENREGISTREMENT (Champs formulaire -> Colonnes BDD)
        $depot->nom_depot = $request->input('nom_depot');
        $depot->adresse_depot = $request->input('secteur_geographique');
        $depot->etat_depot = $request->input('etat_depot'); // Ajout de l'état (opérationnel/inactif/maintenance)
        $depot->open = $request->input('heure_ouverture');
        $depot->close = $request->input('heure_verrouillage');
        $depot->save();
        
        session()->flash('success',$message);
        // 5. RETOUR DU SIGNAL POSITIF VERS L'INTERFACE AJAX
        return response()->json([
            'success' => true,
            'redirect' => route('controldepot')
        ], 200);
        
    }
     /**
     * Enregistre ou modifie simultanément les accès d'un employé dans la table 'users'.
     */
    public function addmodemploye(Request $request)
    {
        // 1. SÉCURITÉ COMPTABLE : Validation stricte du droit admin
        if (!Auth::check() || Auth::user()->role_u !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Accès interdit : Seule la direction peut recruter ou muter du personnel.'
            ], 403);
        }

        // Normalisation préventive du numéro de téléphone avant validation (Facebook Approch)
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
                // Le numéro est déjà au format international, on le garde tel quel
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

        // 2. VALIDATION DES CRITÈRES (Vérification unique de l'email et du téléphone)
        $id_u = $request->input('id_u');
        $validator = Validator::make($request->all(), [
            'nom_u' => 'required|string|max:255',
            'email_u' => 'required|email|max:255|unique:users,email_u,' . $id_u . ',id_u',
            'tel_u' => 'required|string|min:9|max:12|unique:users,tel_u,' . $id_u . ',id_u',
            'adresse_u' => 'required|string|max:255',
            'depot_id' => 'required|exists:depots,id',
            'role_u' => 'required|in:personnel,magasinier,transporteur,chauffeur,manutentionnaire',
        ], [
            'nom_u.required' => 'Le nom complet est obligatoire.',
            'email_u.required' => 'L\'adresse e-mail professionnelle est requise.',
            'email_u.unique' => 'Cette adresse e-mail est déjà attribuée à un autre agent.',
            'tel_u.required' => 'Le numéro de téléphone est requis.',
            'tel_u.min' => 'Le numéro de téléphone doit contenir au moins 9 chiffres.',
            'tel_u.max' => 'Le numéro de téléphone ne peut pas dépasser 12 caractères.',
            'tel_u.unique' => 'Ce numéro de téléphone est déjà relié à un compte.',
            'adresse_u.required' => 'La résidence de l\'employé est obligatoire.',
            'depot_id.required' => 'Veuillez assigner un dépôt d\'affectation.',
            'role_u.required' => 'Veuillez attribuer un poste métier.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // 3. ANALYSE DU SCÉNARIO (MUTATION OU RECRUTEMENT)
        if (!empty($id_u)) {
            // Mode MODIFICATION / MUTATION
            $employee = User::find($id_u);
            if (!$employee) {
                return response()->json(['success' => false, 'message' => 'Agent introuvable.'], 404);
            }
            $message = "Les affectations et accès système de employe ont été mis à jour.";
        } else {
            // Mode RECRUTEMENT / NOUVEAU COMPTE
            $employee = new User();
            // Attribution d'un mot de passe par défaut (Ex: drink2026) que l'employé changera
            $employee->mdp_u = Hash::make('drink2026'); 
            $employee->solde_u = 0.00; // Un employé ne possède pas de compte épargne par défaut
            $message = "Le compte personnel de " . $request->input('nom_u') . " a été créé. Mot de passe initial : drink2026";
        }

        // 4. PERSISTANCE DES DONNÉES DANS LA TABLE USERS
        $employee->nom_u = $request->input('nom_u');
        $employee->email_u = $request->input('email_u');
        $employee->tel_u = $request->input('tel_u');
        $employee->localisation_u = $request->input('adresse_u'); // adresse_u mappe vers ta colonne localisation_u
        
        // Colonnes spécifiques de gestion
        $employee->role_u = 'employe';
        $employee->type_u = $request->input('role_u'); // 'ouvrier' ou 'caissier'
        $employee->depot_id = $request->input('depot_id'); // Colonne de liaison de succursale
        
        $employee->save(); // Sauvegarde MySQL
        session()->flash('success',$message);
        // 5. RETOUR DU SIGNAL POSITIF VERS L'INTERFACE AJAX
        return response()->json([
            'success' => true,
            'redirect' => route('employe')
        ], 200);
    }

    //ajout ou modification d'un client depuis le formulaire de la page client
    public function addmodclient(Request $request)
    {
         // 1. SÉCURITÉ COMPTABLE : Validation stricte du droit admin
         if (!Auth::check() || Auth::user()->role_u !== 'employe') {
            return response()->json([
                'success' => false,
                'message' => 'Accès interdit : Seule l\'employe peut ajouter la clientelle'
            ], 403);
        }
        // Normalisation préventive du numéro de téléphone avant validation (Facebook Approch)
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
                // Le numéro est déjà au format international, on le garde tel quel
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
        // 2. VALIDATION DES CRITÈRES (Vérification unique de l'email et du téléphone)
        $id_c = $request->input('id_c');
        $validator = Validator::make($request->all(), [
            'nom_u' => 'required|string|max:255',
            'email_u' => 'required|email|max:255|unique:users,email_u,' . $id_c . ',id_u', 
            'tel_u' => 'required|string|min:9|max:12|unique:users,tel_u,' . $id_c . ',id_u',
            'localisation_u' => 'required|string|max:255',
        ], [
            'nom_u.required' => 'Le nom complet est obligatoire.',
            'email_u.required' => 'L\'adresse e-mail est requise.',
            'email_u.unique' => 'Cette adresse e-mail est déjà attribuée à un autre client.',
            'tel_u.required' => 'Le numéro de téléphone est requis.',
            'tel_u.min' => 'Le numéro de téléphone doit contenir au moins 9 chiffres.',
            'tel_u.max' => 'Le numéro de téléphone ne peut pas dépasser 12 caractères.',
            'tel_u.unique' => 'Ce numéro de téléphone est déjà relié à un compte.',
            'localisation_u.required' => 'L\'adresse de livraison est obligatoire.',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        // 3. ANALYSE DU SCÉNARIO (MUTATION OU RECRUTEMENT)
        if (!empty($request->input('id_c'))) {
            // Mode MODIFICATION / MUTATION
            $client = User::find($request->input('id_c')); // Recherche le client par son ID
            if (!$client) {
                return response()->json(['success' => false, 'message' => 'Client introuvable.'], 404);
            }
            $message = "Les informations du client ont été mises à jour.";
        } else {
            // Mode RECRUTEMENT / NOUVEAU COMPTE
            $client = new User();
            $client->mdp_u = Hash::make('drink2026'); // Mot de passe par défaut
            $message = "Le compte client de " . $request->input('nom_u') . " a été créé. Mot de passe initial : drink2026";
        }
        // 4. PERSISTANCE DES DONNÉES DANS LA TABLE USERS
        $client->nom_u = $request->input('nom_u');
        $client->email_u = $request->input('email_u');
        $client->tel_u = $request->input('tel_u');
        $client->localisation_u = $request->input('localisation_u'); // adresse
        $client->role_u = 'client';
        $client->type_u = $request->input('type_u', 'particulier'); // Défaut à 'particulier' si non spécifié
        // le client est affecté au depot de l'utilisateur admin qui le crée
        $client->depot_id = Auth::user()->depot_id; // Assigner l'ID du dépôt de l'utilisateur admin qui crée le client
        $client->save(); // Sauvegarde MySQL
        // 5. RETOUR DU SIGNAL POSITIF VERS L'INTERFACE AJAX
        session()->flash('success',$message);
        return response()->json([
            'success' => true,
            'message' => $message,
            'redirect' => route('client')
        ], 200);
    }

    //ajout ou modification d'un produit depuis le formulaire de la page produit
    public function addmodproduit(Request $request)
    {
        $id_p = $request->input('id_p');
        $validator = Validator::make($request->all(), [
            'nom_p' => 'required|string|max:255',
            'format_p' => 'required|nullable|string|max:255',
            'fournisseur_p' => 'required|exists:fournisseurs,id_fournisseur',
            'prix_achat_p' => 'required|numeric|min:0',
            'prix_p' => 'required|numeric|min:0',
        ], [
            'nom_p.required' => 'Le nom du produit est obligatoire.',
            'format_p.required' => 'Le format du produit est obligatoire.',
            'fournisseur_p.required' => 'Veuillez sélectionner un fournisseur.',
            'fournisseur_p.exists' => 'Le fournisseur sélectionné est invalide.',
            'prix_achat_p.required' => 'Le prix d\'achat est requis.',
            'prix_achat_p.numeric' => 'Le prix d\'achat doit être un nombre.',
            'prix_achat_p.min' => 'Le prix d\'achat ne peut pas être négatif.',
            'prix_p.required' => 'Le prix de vente est requis.',
            'prix_p.numeric' => 'Le prix de vente doit être un nombre.',
            'prix_p.min' => 'Le prix de vente ne peut pas être négatif.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        if(empty($id_p)){
            $produit = new Produit();
            $message = "Le produit " . $request->input('nom_p') . " a été ajouté avec succès.";
        }else{
            $produit = Produit::find($id_p);
            if (!$produit) {
                return response()->json(['success' => false, 'message' => 'Produit introuvable.'], 404);
            }
            $message = "Les informations du produit " . $request->input('nom_p') . " ont été mises à jour.";
        }
        $produit->nom_p = $request->input('nom_p');
        $produit->format_p = $request->input('format_p');
        $produit->id_fournisseur = $request->input('fournisseur_p');
        $produit->prix_achat_p = $request->input('prix_achat_p');
        $produit->prix_p = $request->input('prix_p');
        $produit->depot_id = Auth::user()->depot_id; // Assigner l'ID du dépôt de l'utilisateur admin qui crée le produit
        $produit->save();
        session()->flash('success',$message);
        // 5. RETOUR DU SIGNAL POSITIF VERS L'INTERFACE AJAX
        return response()->json([
            'success' => true,
            'redirect' => route('produit')
        ], 200);
    
    }


}