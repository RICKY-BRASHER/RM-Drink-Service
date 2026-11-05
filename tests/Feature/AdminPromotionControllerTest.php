<?php

use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('only an administrator can create a multi-product depot promotion', function () {
    $depotIds = DB::table('depots')->orderBy('id')->pluck('id');
    $supplierId = Fournisseur::query()->value('id_fournisseur');
    $product = Produit::create([
        'nom_p' => 'Boisson test',
        'format_p' => 'Pack 12',
        'prix_achat_p' => 1000,
        'prix_p' => 1500,
        'quantite_p' => 20,
        'seuil' => 10,
        'id_fournisseur' => $supplierId,
        'depot_id' => $depotIds[0],
    ]);

    $employee = User::factory()->create([
        'role_u' => 'employe',
        'type_u' => 'personnel',
        'depot_id' => $depotIds[0],
    ]);

    $this->actingAs($employee)
        ->postJson(route('promotions.store'), [
            'depot_id' => $depotIds[0],
            'type' => 'quantite_offerte',
            'produits' => [$product->id_produit],
            'seuil' => 10,
            'offert' => 1,
            'date_debut' => today()->toDateString(),
            'date_fin' => today()->addDays(7)->toDateString(),
        ])
        ->assertForbidden();

    $admin = User::factory()->create([
        'role_u' => 'admin',
        'type_u' => 'personnel',
    ]);

    $this->actingAs($admin)
        ->postJson(route('promotions.store'), [
            'depot_id' => $depotIds[0],
            'type' => 'quantite_offerte',
            'produits' => [$product->id_produit],
            'seuil' => 10,
            'offert' => 1,
            'date_debut' => today()->toDateString(),
            'date_fin' => today()->addDays(7)->toDateString(),
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

    $this->actingAs($admin)
        ->get(route('promotions.index'))
        ->assertOk();

    $this->assertDatabaseHas('promotion_produit', [
        'produit_id' => $product->id_produit,
    ]);

    $this->actingAs($admin)
        ->postJson(route('promotions.store'), [
            'depot_id' => $depotIds[0],
            'type' => 'quantite_offerte',
            'produits' => [$product->id_produit],
            'seuil' => 10,
            'offert' => 1,
            'date_debut' => today()->toDateString(),
            'date_fin' => today()->addDays(7)->toDateString(),
        ])
        ->assertUnprocessable();

    $this->actingAs($admin)
        ->postJson(route('promotions.store'), [
            'depot_id' => $depotIds[1],
            'type' => 'quantite_offerte',
            'produits' => [$product->id_produit],
            'seuil' => 10,
            'offert' => 1,
            'date_debut' => today()->toDateString(),
            'date_fin' => today()->addDays(7)->toDateString(),
        ])
        ->assertUnprocessable();
});

test('an administrator can create a multi-product discount only for products at the same catalogue price', function () {
    $depotId = DB::table('depots')->value('id');
    $supplierId = Fournisseur::query()->value('id_fournisseur');
    $orange = Produit::create([
        'nom_p' => 'Orange remise',
        'format_p' => 'Pack 12',
        'prix_achat_p' => 1000,
        'prix_p' => 2500,
        'quantite_p' => 20,
        'seuil' => 10,
        'id_fournisseur' => $supplierId,
        'depot_id' => $depotId,
    ]);
    $lemon = Produit::create([
        'nom_p' => 'Citron remise',
        'format_p' => 'Pack 12',
        'prix_achat_p' => 1000,
        'prix_p' => 2500,
        'quantite_p' => 20,
        'seuil' => 10,
        'id_fournisseur' => $supplierId,
        'depot_id' => $depotId,
    ]);
    $otherPrice = Produit::create([
        'nom_p' => 'Boisson autre prix',
        'format_p' => 'Pack 12',
        'prix_achat_p' => 1000,
        'prix_p' => 2600,
        'quantite_p' => 20,
        'seuil' => 10,
        'id_fournisseur' => $supplierId,
        'depot_id' => $depotId,
    ]);
    $admin = User::factory()->create([
        'role_u' => 'admin',
        'type_u' => 'personnel',
    ]);

    $payload = [
        'depot_id' => $depotId,
        'type' => 'reduction_prix',
        'produits' => [$orange->id_produit, $lemon->id_produit],
        'seuil' => 10,
        'prix_promo' => 2400,
        'date_debut' => today()->toDateString(),
        'date_fin' => today()->addDays(7)->toDateString(),
    ];

    $this->actingAs($admin)
        ->postJson(route('promotions.store'), $payload)
        ->assertOk()
        ->assertJson(['success' => true]);

    $this->actingAs($admin)
        ->postJson(route('promotions.store'), [
            ...$payload,
            'produits' => [$orange->id_produit, $otherPrice->id_produit],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('produits');
});

test('employees outside the authorized personnel role cannot open depot management routes', function () {
    $employee = User::factory()->create([
        'role_u' => 'employe',
        'type_u' => 'transporteur',
    ]);

    $this->actingAs($employee)
        ->get('/gestion/stock')
        ->assertForbidden();
});
