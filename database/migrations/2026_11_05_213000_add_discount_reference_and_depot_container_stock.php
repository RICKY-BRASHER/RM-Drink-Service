<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->decimal('prix_reference', 15, 2)->nullable();
        });

        Schema::create('fournisseur_depot_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('depot_id')->constrained('depots')->cascadeOnDelete();
            $table->foreignId('fournisseur_id')
                ->constrained('fournisseurs', 'id_fournisseur')
                ->cascadeOnDelete();
            $table->unsignedInteger('stock_vides')->default(0);
            $table->timestamps();
            $table->unique(['depot_id', 'fournisseur_id']);
        });

        Schema::table('retours_casiers', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('retours_casiers', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable(false)->change();
        });

        Schema::dropIfExists('fournisseur_depot_stocks');

        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn('prix_reference');
        });
    }
};
