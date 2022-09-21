<?php

namespace CoinPipeWCNearGateway\Controllers;

class CurrencyController
{
    public function __construct()
    {
        $this->addActions();
    }

    public function addActions(): void
    {
        add_filter('woocommerce_currencies', [$this, 'addNearCurrency']);
        add_filter('woocommerce_currency_symbol', [$this, 'addNearCurrencySymbol'], 10, 2);
    }

    public function addNearCurrency($currencies)
    {
        $currencies['near'] = __('Near', 'woocommerce');

        return $currencies;
    }

    public function addNearCurrencySymbol($symbol, $currency)
    {
        if ($currency === 'near') {
            $symbol = 'Near';
        }

        return $symbol;
    }
}
