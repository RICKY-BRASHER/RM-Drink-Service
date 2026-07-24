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
        Schema::create('ventes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable(); // client de passage possible
            $table->unsignedBigInteger('employe_id')->nullable(); 
            $table->string('code_facture')->unique(); // ex: FACT-2026-001
            $table->decimal('montant_total', 12, 2);
            $table->decimal('montant_verse', 12, 2)->default(0.00);
            // Mode de paiement : Espece, OM-MOMO, Epargne
            $table->enum('mode_paiement', ['Espece', 'OM-MOMO', 'Epargne']);

            // Statut : payée ou en attente
            $table->enum('statut', ['draft','payer', 'envoyer','attente','annuler'])->default('draft');

            $table->timestamps();

            $table->foreign('client_id')
                ->references('id_u')
                ->on('users')
                ->onDelete('set null');
            $table->foreign('employe_id')->references('id_u')->on('users')->onDelete('cascade');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventes');
    }
};
