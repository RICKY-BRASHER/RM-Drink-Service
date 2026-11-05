<?php

use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\User;
use App\Models\Vente;
use Illuminate\Support\Facades\DB;

function createDeliveryEmployee(int $depotId): User
{
    return User::factory()->create([
        'role_u' => 'employe',
        'type_u' => 'personnel',
        'depot_id' => $depotId,
    ]);
}

function createPaidWalkInSale(User $employee, string $invoiceCode): Vente
{
    return Vente::create([
        'client_id' => null,
        'employe_id' => $employee->id_u,
        'code_facture' => $invoiceCode,
        'montant_total' => 1000,
        'montant_verse' => 1000,
        'mode_paiement' => 'Espece',
        'statut' => 'payer',
    ]);
}

test('a paid walk-in order is queued for delivery instead of being marked delivered at the cashier', function () {
    $depotId = DB::table('depots')->value('id');
    DB::table('depots')->where('id', $depotId)->update([
        'open' => '00:00:00',
        'close' => '23:59:00',
        'etat_depot' => 'operationnel',
    ]);
    $employee = createDeliveryEmployee($depotId);
    $sale = Vente::create([
        'client_id' => null,
        'employe_id' => $employee->id_u,
        'code_facture' => 'TEST-ATTENTE-001',
        'montant_total' => 1000,
        'montant_verse' => 0,
        'mode_paiement' => 'Espece',
        'statut' => 'attente',
    ]);

    $this->actingAs($employee)
        ->postJson("/gestion/commandes-attente/traiter/{$sale->id}", [
            'action' => 'valider',
            'montant_verse' => 1000,
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

    $this->assertDatabaseHas('ventes', [
        'id' => $sale->id,
        'statut' => 'payer',
        'montant_verse' => 1000,
    ]);

    $this->get('/Distrib237/caissier/livraisons')
        ->assertOk()
        ->assertSee('TEST-ATTENTE-001');
});

test('refusing a pending counter order restores reserved stock only once', function () {
    $depotId = DB::table('depots')->value('id');
    DB::table('depots')->where('id', $depotId)->update([
        'open' => '00:00:00',
        'close' => '23:59:00',
        'etat_depot' => 'operationnel',
    ]);
    $supplier = Fournisseur::query()->firstOrFail();
    $employee = createDeliveryEmployee($depotId);
    $product = Produit::create([
        'nom_p' => 'Produit commande annulée',
        'format_p' => 'Pack',
        'prix_achat_p' => 1000,
        'prix_p' => 1500,
        'quantite_p' => 5,
        'seuil' => 1,
        'id_fournisseur' => $supplier->id_fournisseur,
        'depot_id' => $depotId,
    ]);
    $sale = Vente::create([
        'client_id' => null,
        'employe_id' => $employee->id_u,
        'code_facture' => 'TEST-ANNULATION-001',
        'montant_total' => 3000,
        'montant_verse' => 0,
        'mode_paiement' => 'Espece',
        'statut' => 'attente',
    ]);
    DB::table('detail_ventes')->insert([
        'vente_id' => $sale->id,
        'produit_id' => $product->id_produit,
        'quantite' => 2,
        'prix_unitaire' => 1500,
        'total_ligne' => 3000,
        'est_offert' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($employee)
        ->postJson("/gestion/commandes-attente/traiter/{$sale->id}", [
            'action' => 'refuser',
        ])
        ->assertOk();

    expect((float) $product->fresh()->quantite_p)->toBe(7.0);

    $this->postJson("/gestion/commandes-attente/traiter/{$sale->id}", [
        'action' => 'refuser',
    ])->assertNotFound();

    expect((float) $product->fresh()->quantite_p)->toBe(7.0);
});

test('a walk-in sale can be delivered once and its free-container returns are assigned to the depot', function () {
    $depotId = DB::table('depots')->value('id');
    DB::table('depots')->where('id', $depotId)->update([
        'open' => '00:00:00',
        'close' => '23:59:00',
        'etat_depot' => 'operationnel',
    ]);
    $supplier = Fournisseur::query()->firstOrFail();
    $employee = createDeliveryEmployee($depotId);
    $sale = createPaidWalkInSale($employee, 'TEST-LIVRAISON-001');
    $this->actingAs($employee);

    $this->postJson("/Distrib237/caissier/livraisons/valider/{$sale->id}", [
        'rendus' => [$supplier->id_fournisseur => 3],
    ])->assertOk()
        ->assertJson(['success' => true]);

    $this->assertDatabaseHas('ventes', [
        'id' => $sale->id,
        'statut' => 'livrer',
    ]);
    $this->assertDatabaseHas('retours_casiers', [
        'vente_id' => $sale->id,
        'client_id' => null,
        'depot_id' => $depotId,
        'fournisseur_id' => $supplier->id_fournisseur,
        'quantite_rendue' => 3,
    ]);
    $this->assertDatabaseHas('fournisseur_depot_stocks', [
        'depot_id' => $depotId,
        'fournisseur_id' => $supplier->id_fournisseur,
        'stock_vides' => 3,
    ]);

    $this->postJson("/Distrib237/caissier/livraisons/valider/{$sale->id}", [
        'rendus' => [$supplier->id_fournisseur => 3],
    ])->assertNotFound();

    expect((int) $supplier->fresh()->stock_vides_f)->toBe(3)
        ->and(DB::table('retours_casiers')->where('vente_id', $sale->id)->count())->toBe(1);
});

test('a free return can be recorded without a client account', function () {
    $depotId = DB::table('depots')->value('id');
    DB::table('depots')->where('id', $depotId)->update([
        'open' => '00:00:00',
        'close' => '23:59:00',
        'etat_depot' => 'operationnel',
    ]);
    $supplier = Fournisseur::query()->firstOrFail();
    $employee = createDeliveryEmployee($depotId);

    $this->actingAs($employee)
        ->postJson('/Distrib237/caissier/clients/retour-libre', [
            'rendus' => [$supplier->id_fournisseur => 2],
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

    $this->assertDatabaseHas('retours_casiers', [
        'client_id' => null,
        'vente_id' => null,
        'employe_id' => $employee->id_u,
        'depot_id' => $depotId,
        'fournisseur_id' => $supplier->id_fournisseur,
        'quantite_rendue' => 2,
    ]);
    $this->assertDatabaseHas('fournisseur_depot_stocks', [
        'depot_id' => $depotId,
        'fournisseur_id' => $supplier->id_fournisseur,
        'stock_vides' => 2,
    ]);
    expect((int) $supplier->fresh()->stock_vides_f)->toBe(2);
});
