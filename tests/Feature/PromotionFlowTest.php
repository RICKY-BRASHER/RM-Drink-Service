<?php

use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\Promotion;
use App\Models\User;
use App\Models\Vente;
use App\Services\PromotionService;
use Illuminate\Support\Facades\DB;

function createPromotionProduct(int $depotId, int $supplierId, string $name, float $stock): Produit
{
    return Produit::create([
        'nom_p' => $name,
        'format_p' => 'Pack 12',
        'prix_achat_p' => 1000,
        'prix_p' => 1500,
        'quantite_p' => $stock,
        'seuil' => 10,
        'id_fournisseur' => $supplierId,
        'depot_id' => $depotId,
    ]);
}

test('a cumulative promotion adds a random eligible free line without reserving stock', function () {
    $depotId = DB::table('depots')->value('id');
    $supplierId = Fournisseur::query()->value('id_fournisseur');
    $employee = User::factory()->create([
        'role_u' => 'employe',
        'type_u' => 'personnel',
        'depot_id' => $depotId,
    ]);

    $orange = createPromotionProduct($depotId, $supplierId, 'Top Orange', 20);
    $lemon = createPromotionProduct($depotId, $supplierId, 'Top Citron', 20);

    $promotion = Promotion::create([
        'depot_id' => $depotId,
        'type' => 'quantite_offerte',
        'seuil' => 10,
        'offert' => 1,
        'date_debut' => today()->toDateString(),
        'date_fin' => today()->addDays(7)->toDateString(),
        'est_active' => true,
    ]);
    $promotion->produits()->sync([$orange->id_produit, $lemon->id_produit]);

    $sale = Vente::create([
        'client_id' => null,
        'employe_id' => $employee->id_u,
        'code_facture' => 'TEST-PROMO-001',
        'montant_total' => 15000,
        'mode_paiement' => 'Espece',
        'statut' => 'draft',
    ]);

    DB::table('detail_ventes')->insert([
        [
            'vente_id' => $sale->id,
            'produit_id' => $orange->id_produit,
            'quantite' => 6,
            'prix_unitaire' => 1500,
            'total_ligne' => 9000,
            'est_offert' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'vente_id' => $sale->id,
            'produit_id' => $lemon->id_produit,
            'quantite' => 4,
            'prix_unitaire' => 1500,
            'total_ligne' => 6000,
            'est_offert' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    app(PromotionService::class)->syncGiftLines($sale, $depotId);

    $gift = DB::table('detail_ventes')
        ->where('vente_id', $sale->id)
        ->where('est_offert', true)
        ->first();

    expect($gift)->not->toBeNull()
        ->and([$orange->id_produit, $lemon->id_produit])->toContain($gift->produit_id)
        ->and((int) $gift->quantite)->toBe(1)
        ->and((float) $gift->total_ligne)->toBe(0.0)
        ->and((int) $gift->promotion_id)->toBe($promotion->id_promo)
        ->and((float) $orange->fresh()->quantite_p)->toBe(20.0)
        ->and((float) $lemon->fresh()->quantite_p)->toBe(20.0);
});

test('the point of sale prices from the catalogue and deducts gifted stock at checkout', function () {
    $depotId = DB::table('depots')->value('id');
    DB::table('depots')->where('id', $depotId)->update([
        'open' => '00:00:00',
        'close' => '23:59:00',
        'etat_depot' => 'operationnel',
    ]);

    $supplierId = Fournisseur::query()->value('id_fournisseur');
    $employee = User::factory()->create([
        'role_u' => 'employe',
        'type_u' => 'personnel',
        'depot_id' => $depotId,
    ]);
    $orange = createPromotionProduct($depotId, $supplierId, 'Top Orange', 20);
    $lemon = createPromotionProduct($depotId, $supplierId, 'Top Citron', 20);

    $promotion = Promotion::create([
        'depot_id' => $depotId,
        'type' => 'quantite_offerte',
        'seuil' => 10,
        'offert' => 1,
        'date_debut' => today()->toDateString(),
        'date_fin' => today()->addDays(7)->toDateString(),
        'est_active' => true,
    ]);
    $promotion->produits()->sync([$orange->id_produit, $lemon->id_produit]);

    $this->actingAs($employee);

    $firstLine = $this->postJson('/gestion/caisse/ajouter-article', [
        'client_id' => 'walkin',
        'produit_id' => $orange->id_produit,
        'quantite' => 6,
        'prix_unitaire' => 1,
    ])->assertOk()->json();

    $this->postJson('/gestion/caisse/ajouter-article', [
        'vente_id' => $firstLine['vente_id'],
        'client_id' => 'walkin',
        'produit_id' => $lemon->id_produit,
        'quantite' => 4,
        'prix_unitaire' => 1,
    ])->assertOk()
        ->assertJsonCount(3, 'lines')
        ->assertJsonPath('lines.2.est_offert', true);

    $this->postJson('/gestion/caisse/cloturer-vente', [
        'vente_id' => $firstLine['vente_id'],
        'mode_paiement' => 'Espece',
        'statut' => 'payer',
        'montant_verse' => 15000,
        'rendre_monnaie' => 0,
    ])->assertOk();

    expect((float) $orange->fresh()->quantite_p + (float) $lemon->fresh()->quantite_p)->toBe(29.0)
        ->and((float) Vente::findOrFail($firstLine['vente_id'])->montant_total)->toBe(15000.0);
});

test('a promotion cannot sell its qualifying quantity when no eligible gift stock remains', function () {
    $depotId = DB::table('depots')->value('id');
    DB::table('depots')->where('id', $depotId)->update([
        'open' => '00:00:00',
        'close' => '23:59:00',
        'etat_depot' => 'operationnel',
    ]);

    $supplierId = Fournisseur::query()->value('id_fournisseur');
    $employee = User::factory()->create([
        'role_u' => 'employe',
        'type_u' => 'personnel',
        'depot_id' => $depotId,
    ]);
    $product = createPromotionProduct($depotId, $supplierId, 'Top Orange', 10);
    $promotion = Promotion::create([
        'depot_id' => $depotId,
        'type' => 'quantite_offerte',
        'seuil' => 10,
        'offert' => 1,
        'date_debut' => today()->toDateString(),
        'date_fin' => today()->addDays(7)->toDateString(),
        'est_active' => true,
    ]);
    $promotion->produits()->sync([$product->id_produit]);

    $this->actingAs($employee)
        ->postJson('/gestion/caisse/ajouter-article', [
            'client_id' => 'walkin',
            'produit_id' => $product->id_produit,
            'quantite' => 10,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('promotion');

    expect(DB::table('ventes')->count())->toBe(0)
        ->and((float) $product->fresh()->quantite_p)->toBe(10.0);
});

test('a cumulative price promotion applies its reduced price to every eligible unit at checkout', function () {
    $depotId = DB::table('depots')->value('id');
    DB::table('depots')->where('id', $depotId)->update([
        'open' => '00:00:00',
        'close' => '23:59:00',
        'etat_depot' => 'operationnel',
    ]);

    $supplierId = Fournisseur::query()->value('id_fournisseur');
    $employee = User::factory()->create([
        'role_u' => 'employe',
        'type_u' => 'personnel',
        'depot_id' => $depotId,
    ]);
    $orange = createPromotionProduct($depotId, $supplierId, 'Orange remise', 20);
    $lemon = createPromotionProduct($depotId, $supplierId, 'Citron remise', 20);
    $orange->update(['prix_p' => 2500]);
    $lemon->update(['prix_p' => 2500]);

    $promotion = Promotion::create([
        'depot_id' => $depotId,
        'type' => 'reduction_prix',
        'seuil' => 10,
        'prix_promo' => 2400,
        'prix_reference' => 2500,
        'date_debut' => today()->toDateString(),
        'date_fin' => today()->addDays(7)->toDateString(),
        'est_active' => true,
    ]);
    $promotion->produits()->sync([$orange->id_produit, $lemon->id_produit]);

    $this->actingAs($employee);
    $firstLine = $this->postJson('/gestion/caisse/ajouter-article', [
        'client_id' => 'walkin',
        'produit_id' => $orange->id_produit,
        'quantite' => 6,
    ])->assertOk()->json();

    $this->postJson('/gestion/caisse/ajouter-article', [
        'vente_id' => $firstLine['vente_id'],
        'client_id' => 'walkin',
        'produit_id' => $lemon->id_produit,
        'quantite' => 4,
    ])->assertOk()
        ->assertJsonPath('lines.0.prix_unitaire', 2400)
        ->assertJsonPath('lines.1.prix_unitaire', 2400);

    $this->postJson('/gestion/caisse/cloturer-vente', [
        'vente_id' => $firstLine['vente_id'],
        'mode_paiement' => 'Espece',
        'statut' => 'payer',
        'montant_verse' => 24000,
        'rendre_monnaie' => 0,
    ])->assertOk();

    expect((float) Vente::findOrFail($firstLine['vente_id'])->montant_total)->toBe(24000.0)
        ->and((float) $orange->fresh()->quantite_p + (float) $lemon->fresh()->quantite_p)->toBe(30.0)
        ->and(DB::table('detail_ventes')->where('vente_id', $firstLine['vente_id'])
            ->where('promotion_id', $promotion->id_promo)->count())->toBe(2);
});
