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
        Schema::create('detail_approvisionnements', function (Blueprint $table) {
            $table->id('id_det_a');
            $table->bigInteger('approvisionnement_id')->unsigned(); // Lien vers le Bon
            $table->bigInteger('produit_id')->unsigned(); // La boisson concernée
            $table->integer('quantite_ajoutee'); // La quantité livrée
            $table->timestamps();

            $table->foreign('approvisionnement_id')->references('id_app')->on('approvisionnements')->onDelete('cascade');
            $table->foreign('produit_id')->references('id_produit')->on('produits')->onDelete('cascade');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_approvisionnements');
    }
};
