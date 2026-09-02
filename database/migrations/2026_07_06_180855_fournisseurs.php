<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("fournisseurs", function (Blueprint $table) {
            $table->id("id_fournisseur");
            $table->string("nom_fournisseur", 100);
            $table->integer('stock_vides_f')->default(0);
            $table->timestamps();
        });
        // Insertion de données initiales dans la table "fournisseurs" 
        DB::table("fournisseurs")->insert([
            ["nom_fournisseur" => "SABC"],
            ["nom_fournisseur" => "Guinness"],
            ["nom_fournisseur" => "UCB"],
            ["nom_fournisseur" => "SOURCE DU PAYS"],
            ["nom_fournisseur" => "AUTRES"],
            
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("fournisseurs");
    }
};
