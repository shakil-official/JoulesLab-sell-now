<?php

namespace SellNow\Services\Payments\Gateways;

use SellNow\Contracts\PaymentGatewayInterface;

class PayPalGateway implements PaymentGatewayInterface
{
    public function name(): string
    {
        return 'PayPal';
    }
}

