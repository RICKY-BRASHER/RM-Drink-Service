<?php

namespace Database\Factories;

use App\Models\produit;
use App\Models\fournisseur;
use App\Models\depot;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProduitFactory extends Factory
{
    protected $model = Produit::class;

    public function definition(): array
    {
        // Banques de données réelles pour des tests immersifs au Cameroun
        $boissons = [
            'Beaufort Light', 'Castel Beer', '33 Export', 'Mutzig', 'Guinness Brune', 
            'Malta Guinness', 'Kadji Beer', 'Jus Planet Orange', 'Jus Planet Top', 
            'Pamplemousse', 'Vimto', 'Djino Tonic', 'Eau Tangui', 'Supermont'
        ];

        $formats = ['casier 12', 'casier 24', 'pack 6', 'pack 12', 'bidon'];

        // On récupère au hasard un fournisseur et un dépôt existant en BDD
        $id_fournisseur = Fournisseur::inRandomOrder()->first()?->id_fournisseur ?? 1;
        //on choisit le depot avec l'id aleatoire entre 1 et 3  
        $depot_id = Depot::inRandomOrder()->first()?->id ?? 1;// Génère un prix d'achat aléatoire entre 3500 et 9500

        $prixAchat = $this->faker->numberBetween(3500, 9500);

        return [
            'nom_p' => $this->faker->randomElement($boissons) . ' ' . $this->faker->word(), // Génère un nom de produit unique en combinant une boisson et un mot aléatoire 
            'format_p' => $this->faker->randomElement($formats),
            'prix_achat_p' => $prixAchat,
            'prix_p' => $prixAchat + $this->faker->numberBetween(500, 1500), // Marge comptable
            'quantite_p' => $this->faker->numberBetween(10, 500), // Stock initial aléatoire
            'id_fournisseur' => $id_fournisseur,
            'depot_id' => $depot_id,
        ];
    }
}
