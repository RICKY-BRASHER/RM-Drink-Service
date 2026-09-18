<?php

namespace App\Http\Controllers;
use App\Models\Produit;
use App\Models\Fournisseur;
use App\Models\Vente;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
class ClientShow extends Controller
{
    /*public function showaccueil()
{
    $client = Auth::user();
    
    $currentMonth = Carbon::now()->month;
    $currentYear = Carbon::now()->year;

    // 1. Nombre de commandes envoyées ou payées par ce client durant le mois en cours
    $commandesMois = DB::table('ventes')
        ->where('client_id', $client->id_u)
        ->whereIn('statut', ['attente', 'payer'])
        ->whereMonth('created_at', $currentMonth)
        ->whereYear('created_at', $currentYear)
        ->count();

    // 2. Budget global consommé (Montant total des factures traitées ce mois)
    $budgetConsomme = DB::table('ventes')
        ->where('client_id', $client->id_u)
        ->whereIn('statut', ['attente', 'payer'])
        ->whereMonth('created_at', $currentMonth)
        ->whereYear('created_at', $currentYear)
        ->sum('montant_total');

    // 3. RÈGLE COMMERCIALE DU CASHBACK : 1% du montant investi reversé virtuellement
    $cashbackVirtuel = round($budgetConsomme * 0.01);

    return view('layout.client.accueil', compact('client', 'commandesMois', 'budgetConsomme', 'cashbackVirtuel'));
}


    public function showcommande()
    {
        $client = Auth::user();

        $fournisseurs = Fournisseur::orderBy('id_fournisseur', 'asc')->get();
        $boissons = Produit::where('depot_id', $client->depot_id)->orderBy('nom_p', 'asc')->get();

        // 🔍 On cherche si ce client a déjà un brouillon non validé en cours
        $brouillon = Vente::where('client_id', $client->id_u)
            ->where('statut', 'draft')
            ->first();

        $lignesPanier = [];
        $totalBrouillon = 0;

        if ($brouillon) {
            // On récupère les lignes du panier déjà enregistrées en BDD
            $lignesPanier = DB::table('detail_ventes')
                ->join('produits', 'detail_ventes.produit_id', '=', 'produits.id_produit')
                ->where('detail_ventes.vente_id', $brouillon->id)
                ->select('detail_ventes.*', 'produits.nom_p', 'produits.format_p', 'produits.prix_p')
                ->get();
            
            $totalBrouillon = $brouillon->montant_total;
        }

        return view('layout.client.commande', compact('boissons', 'fournisseurs', 'brouillon', 'lignesPanier', 'totalBrouillon'));
    }

    public function showtransactions(){
         $client = auth()->user();

    // Extraction de tout l'historique financier depuis la table recharges
    $mouvements = DB::table('recharges')
        ->where('client_id', $client->id_u)
        ->orderBy('created_at', 'desc')
        ->get();

    // 🚨 LE CORRECTIF : On envoie la variable 'mouvements' à la vue
    return view('layout.client.transactions', compact('mouvements'));
    }

    public function showprofil(){
        return view("layout.client.profil");
    }
    public function showsuivie(){
        $client = Auth::user();

        // Récupération de l'historique d'achats (Exclure le statut 'brouillon' qui reste masqué)
        $achats = Vente::where('client_id', $client->id_u)
            ->where('statut', '!=', 'draft')
            ->orderBy('created_at', 'desc')
            ->get();

        // Envoi de la variable $achats à la vue suivie
        return view('layout.client.suivie', compact('achats'));
    }

    public function shownotif(){
        return view("layout.client.notif");
    }

    public function ajouterAuPanierBrouillon(Request $request)
    {
        $request->validate([
            'produit_id' => 'required|integer',
            'quantite'   => 'required|numeric|min:0.25'
        ]);

        $client = Auth::user();
        $produitId = $request->input('produit_id');
        $qte = (float)$request->input('quantite');

        $produit = Produit::where('id_produit', $produitId)->where('depot_id', $client->depot_id)->first();
        if (!$produit || $produit->quantite_p < $qte) {
            return response()->json(['success' => false, 'message' => "Stock insuffisant au dépôt."], 422);
        }

        DB::beginTransaction();
        try {
            // 1. Récupérer ou Créer le brouillon maître de la vente
            $vente = Vente::firstOrCreate(
                ['client_id' => $client->id_u, 'statut' => 'draft'],
                [
                    'code_facture'  => 'BRN-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                    'montant_total' => 0,
                    'montant_verse' => 0
                ]
            );

            $totalLigne = $qte * (float)$produit->prix_p;

            // 2. 💡 RÈGLE DU DOUBLON : updateOrInsert (Si existe, écrase et remplace la quantité)
            DB::table('detail_ventes')->updateOrInsert(
                ['vente_id' => $vente->id, 'produit_id' => $produitId],
                [
                    'quantite'      => $qte,
                    'prix_unitaire' => $produit->prix_p,
                    'total_ligne'   => $totalLigne,
                    'updated_at'    => now(),
                    'created_at'    => now() 
                ]
            );

            // 3. Recalculer le montant total global du brouillon de vente
            $nouveauTotal = DB::table('detail_ventes')->where('vente_id', $vente->id)->sum('total_ligne');
            $vente->montant_total = $nouveauTotal;
            $vente->save();

            // Compter le nombre de lignes distinctes pour le badge
            $badgeCount = DB::table('detail_ventes')->where('vente_id', $vente->id)->count();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Produit mis à jour dans votre panier.",
                'badge_count' => $badgeCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function supprimerDuPanier($produitId)
    {
        $client = Auth::user();
        
        $vente = Vente::where('client_id', $client->id_u)->where('statut', 'draft')->first();
        if (!$vente) return response()->json(['success' => false, 'message' => "Aucun panier actif."], 422);

        DB::beginTransaction();
        try {
            // Suppression de la ligne dans detail_ventes
            DB::table('detail_ventes')->where('vente_id', $vente->id)->where('produit_id', $produitId)->delete();

            // Recalcul du total de la facture
            $nouveauTotal = DB::table('detail_ventes')->where('vente_id', $vente->id)->sum('total_ligne');
            $vente->montant_total = $nouveauTotal;
            $vente->save();

            $badgeCount = DB::table('detail_ventes')->where('vente_id', $vente->id)->count();

            // Si le panier se retrouve complètement vide, on nettoie le squelette de la vente
            if ($badgeCount === 0) {
                $vente->delete();
            }

            DB::commit();
            return response()->json(['success' => true, 'badge_count' => $badgeCount, 'nouveau_total' => $nouveauTotal]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function recupererDetailsBon($id)
    {
        try {
            $lignes = DB::table('detail_ventes')
                ->join('produits', 'detail_ventes.produit_id', '=', 'produits.id_produit')
                ->where('detail_ventes.vente_id', $id)
                ->select('detail_ventes.quantite', 'detail_ventes.total_ligne', 'produits.nom_p', 'produits.prix_p', 'produits.format_p')
                ->get();

            // Formatage des lignes pour simplifier le travail du JQuery
            $lignesFormatees = $lignes->map(function($l) {
                $fmt = strtolower($l->format_p);
                $sfx = str_contains($fmt, 'casier') ? 'cs' : (str_contains($fmt, 'bidon') ? 'bidon' : 'pack');
                $qteFmt = ($l->quantite == floor($l->quantite)) ? (int)$l->quantite : number_format($l->quantite, 2, ',', ' ');
                
                return [
                    'nom_p' => $l->nom_p,
                    'prix_p' => $l->prix_p,
                    'total_ligne' => $l->total_ligne,
                    'quantite_affichee' => $qteFmt . ' ' . $sfx
                ];
            });

            return response()->json(['success' => true, 'lignes' => $lignesFormatees], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    /**
 * Fait basculer le panier de 'brouillon' à 'envoyer' avec barrière anti-dette
 */
   /* public function finaliserPanierEnvoyer(Request $request)
    {
        $client = auth()->user();
        
        // 🚨 BARRIÈRE DE SÉCURITÉ FINANCIÈRE SERVEUR : Dette supérieure à 5000 F
        if ($client->solde_u < -5000) {
            return response()->json([
                'success' => false, 
                'message' => "🔒 Envoi refusé. Votre encours débiteur actuel (" . number_format(abs($client->solde_u), 0, ',', ' ') . " F) excède la limite tolérée de 5 000 F."
            ], 422); // Renvoie un code 422 qui sera intercepté par le bloc 'error:' de l'AJAX
        }

        $vente = Vente::where('client_id', $client->id_u)->where('statut', 'draft')->first();
        if (!$vente) return response()->json(['success' => false, 'message' => "Panier vide."], 422);

        DB::beginTransaction();
        try {
            $vente->code_facture = 'CMD-' . date('Ymd') . '-' . strtoupper(Str::random(4));
            $vente->statut = 'envoyer'; 
            DB::table('notifications_systeme')->insert([
                'depot_id'   => auth()->user()->depot_id,
                'titre_n'    => "📥 Nouvelle commande reçue",
                'message_n'  => "Le client " . strtoupper(auth()->user()->nom_u) . " a transmis une commande en ligne d'un montant de " . number_format($vente->montant_total, 0, ',', ' ') . " F.",
                'icone_n'    => "bi-file-earmark-arrow-down-fill",
                'couleur_n'  => "text-primary",
                'created_at' => now()
            ]);

            $vente->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => "Votre commande {$vente->code_facture} a été envoyée au secrétariat avec succès !"]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    public function modifierAvatarClient(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // 2 Mo max
        ]);

        try {
            $client = auth()->user();

            if ($request->hasFile('avatar')) {
                $image = $request->file('avatar');
                
                // Génération d'un nom unique (Ex: AVATAR-6161-171829.jpg)
                $filename = 'AVATAR-' . $client->id_u . '-' . time() . '.' . $image->getClientOriginalExtension();

                // Nettoyage de l'ancienne photo sur le disque du serveur s'il y en avait une
                if ($client->avatar_u) {
                    Storage::disk('public')->delete('avatars/' . $client->avatar_u);
                }

                // Stockage physique de la nouvelle image dans storage/app/public/avatars/
                $image->storeAs('avatars', $filename, 'public');

                // Mise à jour de la colonne en base de données via Query Builder pour aller vite
                DB::table('users')->where('id_u', $client->id_u)->update([
                    'avatar_u' => $filename,
                    'updated_at' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Votre photo de profil a été mise à jour avec succès !",
                    'avatar_name' => $filename
                ], 200);
            }

            return response()->json(['success' => false, 'message' => "Aucun fichier reçu."], 400);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "Erreur serveur : " . $e->getMessage()], 500);
        }
    }

    public function modifierMotDePasseClient(Request $request)
    {
        // 1. Règles de validation haut de gamme conformes à tes critères
        $request->validate([
            'old_password' => 'required',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->numbers(), // Minimum 8 caractères + Lettre + Chiffre (anti-facile)
            ],
        ], [
            'password.confirmed' => 'La confirmation du nouveau mot de passe ne correspond pas.',
            'password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
        ]);

        try {
            // Récupération de l'instance de l'utilisateur avec sa clé réelle id_u
            $client = auth()->user();

            // 2. Vérification A : L'ancien mot de passe saisi est-il correct ?
            if (!Hash::check($request->input('old_password'), $client->mdp_u)) {
                return response()->json([
                    'errors' => ['old_password' => ["L'ancien mot de passe est incorrect."]]
                ], 422);
            }

            // 3. Vérification B : Le nouveau mot de passe est-il identique à l'ancien ?
            if (Hash::check($request->input('password'), $client->mdp_u)) {
                return response()->json([
                    'errors' => ['password' => ["Le nouveau mot de passe ne peut pas être identique à l'actuel."]]
                ], 422);
            }

            // 4. Validation et Cryptage (Bcrypt) du nouveau mot de passe dans la table users
            DB::table('users')->where('id_u', $client->id_u)->update([
                'mdp_u' => Hash::make($request->input('password')),
                'updated_at' => now()
            ]);

            // 📱 Notification instantanée dans la cloche du smartphone du client
            DB::table('notifications_systeme')->insert([
                'user_id' => $client->id_u,
                'titre_n' => "🔒 Sécurité : Accès mis à jour",
                'message_n' => "Le mot de passe de votre compte de boisson Distrib237 a été modifié avec succès.",
                'icone_n' => "bi-key-fill",
                'couleur_n' => "text-warning",
                'statut_lecture' => 'non_lu',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Vos clés d'accès ont été mises à jour avec succès !"
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "Erreur système : " . $e->getMessage()], 500);
        }
    }*/
}
