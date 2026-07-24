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
        Schema::create('approvisionnements', function (Blueprint $table) {
            $table->id('id_app');
            $table->bigInteger('id_u_admin')->unsigned(); // L'admin qui a validé
            $table->bigInteger('id_fournisseur')->unsigned(); // Le fournisseur (SABC, Guinness...)
            $table->bigInteger('depot_id')->unsigned();
            $table->integer('total_boissons')->default(0); // Nb total de blles reçues
            $table->timestamps(); // Date et heure de livraison (created_at)
            $table->foreign('id_u_admin')->references('id_u')->on('users')->onDelete('cascade'); //  
            $table->foreign('id_fournisseur')->references('id_fournisseur')->on('fournisseurs')->onDelete('cascade');
            $table->foreign('depot_id')->references('id')->on('depots')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvisionnements');
    }
};
