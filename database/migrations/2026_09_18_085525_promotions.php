<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id('id_promo');
            $table->string('type'); // "quantite_offerte" ou "reduction_prix"
            
            // Cible : Soit un produit spécifique, soit un mot-clé de marque
            $table->foreignId('produit_id')->nullable()->constrained('produits', 'id_produit')->onDelete('cascade');
            $table->foreignId('depot_id')->nullable()->constrained('depots')->onDelete('cascade');
            $table->string('marque')->nullable(); // ex: "Planet", "Opur" (Recherche textuelle dans le nom)
            
            $table->integer('seuil'); // ex: 10
            $table->integer('offert')->nullable(); // ex: 1 ou 2
            $table->decimal('prix_promo', 15, 2)->nullable(); // ex: 1100.00
            
            $table->date('date_debut');
            $table->date('date_fin');
            $table->boolean('est_active')->default(true); // Pour activer/désactiver la promotion sans la supprimer
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
