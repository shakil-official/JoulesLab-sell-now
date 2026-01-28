<?php

namespace SellNow\Services\Payments;

use SellNow\Contracts\PaymentGatewayInterface;
use SellNow\Services\Payments\Gateways\StripeGateway;
use SellNow\Services\Payments\Gateways\PayPalGateway;
use SellNow\Services\Payments\Gateways\RazorpayGateway;
use InvalidArgumentException;

class PaymentGatewayFactory
{
    /**
     * All supported payment gateways
     */
    public static function all(): array
    {
        return [
            new StripeGateway(),
            new PayPalGateway(),
            new RazorpayGateway(),
        ];
    }

    /**
     * Resolve a gateway by name
     */
    public static function make(string $provider): PaymentGatewayInterface
    {
        foreach (self::all() as $gateway) {
            if ($gateway->name() === $provider) {
                return $gateway;
            }
        }

        throw new InvalidArgumentException('Unsupported payment provider');
    }
}
