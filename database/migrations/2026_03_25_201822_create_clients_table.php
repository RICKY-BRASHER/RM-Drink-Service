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
        Schema::create('clients', function (Blueprint $table) {
            $table->string('id_c')->primary();
            $table->string('nom_c',20);
            $table->string('adresse_c',);
            $table->string('tel_c',15);
            $table->unsignedBigInteger('id_u');
            $table->float('solde_c')->default(0);
            $table->foreign('id_u')->references('id_u')->on('utilisateurs')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
