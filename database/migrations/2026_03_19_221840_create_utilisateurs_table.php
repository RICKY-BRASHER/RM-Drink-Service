<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('depots', function (Blueprint $table) {
            $table->id();
            $table->string('nom_depot');
            $table->string('adresse_depot');
            $table->time('open');
            $table->time('close');
            $table->enum('etat_depot', ['operationnel', 'inactif', 'en_maintenance'])->default('operationnel');
            $table->timestamps();
        });
        DB::table('depots')->insert([
            'nom_depot' => 'Depot Central',
            'adresse_depot' => '123 Main Street, Cityville',
            'open' => '08:00:00',
            'close' => '18:00:00',
            'etat_depot' => 'operationnel',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('depots')->insert([
            'nom_depot' => 'Depot Nord',
            'adresse_depot' => '456 North Avenue, Cityville',
            'open' => '09:00:00',
            'close' => '17:00:00',
            'etat_depot' => 'operationnel',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('depots')->insert([
            'nom_depot' => 'Depot Sud',
            'adresse_depot' => '789 South Road, Cityville',
            'open' => '07:30:00',
            'close' => '19:00:00',
            'etat_depot' => 'operationnel',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('users', function (Blueprint $table) {
            $table->bigInteger('id_u')->unsigned()->primary(); // 
            $table->string('nom_u');
            $table->string('localisation_u')->nullable();
            $table->string('email_u')->unique();
            $table->string('tel_u')->unique();
            $table->enum('role_u', ['client', 'employe' , 'admin'])->default('client');
            $table->enum('type_u', ['particulier', 'Depot','tricycle', 'personnel', 'magasinier', 'transporteur', 'chauffeur', 'manutentionnaire' ])->default('particulier');
            $table->foreignId('depot_id')->nullable()->constrained('depots')->onDelete('cascade')->onUpdate('cascade');
            $table->decimal('solde_u', 10, 2)->default(0);
            $table->string('mdp_u')->nullable();
            $table->string('avatar_u')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // Creation de l'utilisateur admin par défaut
        User::create([
            'nom_u' => 'Admin',
            'localisation_u' => 'Admin Location',
            'email_u' => 'admin@example.com',
            'tel_u' => '237690408038',
            'role_u' => 'admin',
            'type_u' => 'personnel',
            'depot_id' => '1',
            'mdp_u' => bcrypt('admin123'),
            'created_at' => now(), 
            'updated_at' => now(),
        ]);

        // Creation de l'utilisateur client par défaut
        User::create([
            'nom_u' => 'Client',
            'localisation_u' => 'Client Location',
            'email_u' => 'client@example.com',
            'tel_u' => '237693952476',
            'role_u' => 'client',
            'type_u' => 'particulier',
            'depot_id' => '1',
            'mdp_u' => bcrypt('client123'),
            'created_at' => now(), 
            'updated_at' => now(),
            
        ]);

        // Creation de l'utilisateur employe par défaut
        User::create([
            'nom_u' => 'Employe',
            'localisation_u' => 'Employe Location', 
            'email_u' => 'employe@example.com',
            'tel_u' => '237693952477',
            'role_u' => 'employe',
            'type_u' => 'personnel',
            'depot_id' => '1',
            'mdp_u' => bcrypt('employe123'),
            'created_at' => now(), 
            'updated_at' => now(),
        ]);
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS chk_tel_u_cameroun");
        }
        Schema::dropIfExists('users');
        Schema::dropIfExists('depot');
    }
};
