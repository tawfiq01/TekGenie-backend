<?php

use App\Enums\OrderStatus;

it('allows the documented happy-path transitions', function () {
    expect(OrderStatus::Pending->canTransitionTo(OrderStatus::Confirmed))->toBeTrue()
        ->and(OrderStatus::Confirmed->canTransitionTo(OrderStatus::Processing))->toBeTrue()
        ->and(OrderStatus::Processing->canTransitionTo(OrderStatus::Shipped))->toBeTrue()
        ->and(OrderStatus::Shipped->canTransitionTo(OrderStatus::Delivered))->toBeTrue()
        ->and(OrderStatus::Shipped->canTransitionTo(OrderStatus::Returned))->toBeTrue()
        ->and(OrderStatus::Delivered->canTransitionTo(OrderStatus::Returned))->toBeTrue();
});

it('refuses skipping straight to Delivered from Pending (AC-ORD-01)', function () {
    expect(OrderStatus::Pending->canTransitionTo(OrderStatus::Delivered))->toBeFalse();
});

it('treats Cancelled and Returned as terminal', function () {
    expect(OrderStatus::Cancelled->isTerminal())->toBeTrue()
        ->and(OrderStatus::Returned->isTerminal())->toBeTrue()
        ->and(OrderStatus::Cancelled->allowedTransitions())->toBe([])
        ->and(OrderStatus::Returned->allowedTransitions())->toBe([]);
});
