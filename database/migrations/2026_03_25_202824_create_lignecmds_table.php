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
        Schema::create('lignecmds', function (Blueprint $table) {
            $table->id('id_lgc');
            $table->unsignedBigInteger('id_p');
            $table->foreign('id_p')->references('id_p')->on('produits')->constrained()->onDelete('no action')->onUpdate('cascade');
            $table->float('qte_p');
            $table->float('prix_t');
            $table->unsignedBigInteger('id_v');
            $table->unsignedBigInteger('id_u');
            $table->foreign('id_v')->references('id_v')->on('commandes')->constrained()->onDelete('cascade');
            $table->foreign('id_u')->references('id_u')->on('utilisateurs')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lignecmds');
    }
};
