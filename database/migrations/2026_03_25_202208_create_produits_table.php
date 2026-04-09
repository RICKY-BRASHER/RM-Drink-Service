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
        Schema::create('produits', function (Blueprint $table) {
            $table->id('id_p');
            $table->string('nom_p');
            $table->bigInteger('prix_p');
            $table->string('categorie');
            $table->float('qte_p');
            $table->unsignedBigInteger('id_u');
            $table->unsignedBigInteger('ref_f');
            $table->foreign('ref_f')->references('ref_f')->on('fournisseurs')->constrained()->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('id_u')->references('id_u')->on('utilisateurs')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
