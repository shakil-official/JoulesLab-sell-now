<?php

namespace SellNow\Services\Payments\Gateways;

use SellNow\Contracts\PaymentGatewayInterface;

class StripeGateway implements PaymentGatewayInterface
{
    public function name(): string
    {
        return 'Stripe';
    }
}