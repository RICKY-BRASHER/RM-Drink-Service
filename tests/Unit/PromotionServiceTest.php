<?php

use App\Services\PromotionService;

test('gift quantities are calculated for each complete cumulative threshold', function () {
    $service = new PromotionService();

    expect($service->giftQuantityFor(9.99, 10, 1))->toBe(0)
        ->and($service->giftQuantityFor(10, 10, 1))->toBe(1)
        ->and($service->giftQuantityFor(20, 10, 1))->toBe(2)
        ->and($service->giftQuantityFor(19.99, 10, 2))->toBe(2);
});

test('invalid promotion quantities never create gifts', function () {
    $service = new PromotionService();

    expect($service->giftQuantityFor(100, 0, 1))->toBe(0)
        ->and($service->giftQuantityFor(100, 10, 0))->toBe(0);
});
