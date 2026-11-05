<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_produit', function (Blueprint $table) {
            $table->foreignId('promotion_id')->constrained('promotions', 'id_promo')->cascadeOnDelete();
            $table->foreignId('produit_id')->constrained('produits', 'id_produit')->cascadeOnDelete();
            $table->primary(['promotion_id', 'produit_id']);
        });

        Schema::table('detail_ventes', function (Blueprint $table) {
            $table->boolean('est_offert')->default(false)->after('total_ligne');
            $table->foreignId('promotion_id')
                ->nullable()
                ->after('est_offert')
                ->constrained('promotions', 'id_promo')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('detail_ventes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promotion_id');
            $table->dropColumn('est_offert');
        });

        Schema::dropIfExists('promotion_produit');
    }
};
