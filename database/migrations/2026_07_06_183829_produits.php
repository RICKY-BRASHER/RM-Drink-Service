<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Code pour créer la table "produits" avec les colonnes nécessaires 
        Schema::create('produits', function (Blueprint $table) {
            $table->id('id_produit');
            $table->string('nom_p', 100);
            $table->string('format_p', 50);
            $table->decimal('prix_achat_p', 15, 2);
            $table->decimal('prix_p', 15, 2);
            $table->decimal('quantite_p', 15, 2)->default(0);
            $table->integer('seuil')->default(20);
            $table->foreignId('id_fournisseur')
                ->constrained('fournisseurs', 'id_fournisseur') // 💡 "id_fournisseur" est précisé ici
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreignId('depot_id')->constrained('depots')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
