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
        Schema::create('commandes', function (Blueprint $table) {
            $table->id('id_v');
            $table->string('descrip_v');
            $table->float('prix_total');
            $table->string('type_paie');
            $table->string('etat');
            $table->date('date_v');
            $table->string('id_c',20);
            $table->unsignedBigInteger('id_u');
            $table->foreign('id_c')->references('id_c')->on('clients')->constrained()->onDelete('no action')->onUpdate('cascade');
            $table->foreign('id_u')->references('id_u')->on('utilisateurs')->constrained()->onDelete('cascade');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
