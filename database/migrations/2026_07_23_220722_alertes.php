<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertes_stocks', function (Blueprint $table) {
            $table->id('id_alerte');
            $table->foreignId('produit_id')->constrained('produits', 'id_produit')->onDelete('cascade');
            $table->foreignId('depot_id')->constrained('depots', 'id')->onDelete('cascade');
            $table->decimal('quantite_restante', 15, 2);
            $table->integer('seuil_limite');
            $table->enum('statut_lecture', ['non_lu', 'lu'])->default('non_lu');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertes_stocks');
    }
};
