<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications_systeme', function (Blueprint $table) {
            $table->id('id_notif');
            // Colonnes configurées en nullable() selon votre SQL
            $table->foreignId('user_id')->nullable()->constrained('users', 'id_u')->onDelete('cascade');
            $table->foreignId('depot_id')->nullable()->constrained('depots', 'id')->onDelete('cascade');
            $table->string('titre_n', 150);
            $table->text('message_n');
            $table->string('icone_n', 50)->default('bi-bell-fill');
            $table->string('couleur_n', 30)->default('text-primary');
            $table->enum('statut_lecture', ['non_lu', 'lu'])->default('non_lu');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications_systeme');
    }
};

