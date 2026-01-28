<?php

namespace SellNow\Contracts;

interface PaymentGatewayInterface
{
    public function name(): string;
}
