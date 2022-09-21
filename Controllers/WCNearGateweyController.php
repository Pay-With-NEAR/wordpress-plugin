<?php

namespace CoinPipeWCNearGateway\Controllers;

class WCNearGateweyController
{
    public function __construct()
    {
        $this->addActions();
    }

    public function addActions(): void
    {
        add_filter('woocommerce_payment_gateways', [$this, 'addGateways']);
    }

    public function addGateways($gateways)
    {
        if (in_array(get_option('woocommerce_currency'), ['near', 'USD', 'EUR'])) {
            $gateways[] = 'CoinPipeWCNearGateway\Model\WCNearGateway';
        }
        return $gateways;
    }
}