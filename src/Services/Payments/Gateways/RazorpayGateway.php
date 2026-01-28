<?php

namespace SellNow\Services\Payments\Gateways;

use SellNow\Contracts\PaymentGatewayInterface;

class RazorpayGateway implements PaymentGatewayInterface
{
    public function name(): string
    {
        return 'Razorpay';
    }
}
