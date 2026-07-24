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
        Schema::create('recharges', function (Blueprint $table) {
            $table->id(); // ID unique de la transaction
            $table->string('reference_tx', 50)->unique();
            $table->bigInteger('client_id')->unsigned(); // Le client qui reçoit l'argent
            $table->unsignedBigInteger('vente_id')->nullable();
            $table->bigInteger('employe_id')->unsigned(); // L'employé/admin qui encaisse le cash
            $table->decimal('montant_r', 15, 2); // Le montant versé
            $table->decimal('solde_avant_r', 15, 2);
            $table->decimal('solde_apres_r', 15, 2); // Le nouveau solde du client (Sécurité d'audit)
             $table->enum('type_mouvement', ['recharge', 'achat_direct', 'creation_dette'])
                  ->default('recharge');
            // Mode de versement : Especes, MTN Mobile Money, Orange Money
            $table->enum('moyen_recharge', ['especes', 'momo', 'om','annulation_facture']); 
            
            
            $table->string('ip_adresse', 45)->nullable();
             
            $table->timestamps(); // Génère automatiquement created_at (Date et heure exacte)

            // Liaisons de sécurité avec vos tables existantes
            $table->foreign('client_id')->references('id_u')->on('users')->onDelete('cascade');
            $table->foreign('employe_id')->references('id_u')->on('users')->onDelete('cascade');
            $table->foreign('vente_id')->references('id')->on('ventes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recharges');
    }
};
