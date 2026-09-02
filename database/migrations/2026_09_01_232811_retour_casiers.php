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
        Schema::create('retours_casiers', function (Blueprint $table) {
            $table->id('id_rc');
            $table->foreignId('client_id')->constrained('users', 'id_u')->onDelete('cascade');
            $table->foreignId('employe_id')->constrained('users', 'id_u')->onDelete('cascade');
            $table->foreignId('depot_id')->constrained('depots', 'id')->onDelete('cascade');
            $table->foreignId('fournisseur_id')->constrained('fournisseurs', 'id_fournisseur')->onDelete('cascade');
            
            // 💡 NULLABLE : Lié à une vente ou retour libre en différé
            $table->foreignId('vente_id')->nullable()->constrained('ventes', 'id')->onDelete('set null');
            
            // 📊 La quantité physique de casiers retournés pour CE fournisseur sur cette ligne
            $table->integer('quantite_rendue')->default(0);
            
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retours_casiers');
    }
};
