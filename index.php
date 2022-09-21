<?php
/*
 * Plugin Name: Coin Pipe WooCommerce Near payment Gateway
 * Description: pay with near
 * Author: coin pipe
 * Author URI: https://app.coinpipe.finance/
 * Version: 0.0.1
 */

use CoinPipeWCNearGateway\Model\Constructor\Constructor;

if (!function_exists('is_plugin_active')) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

try {
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
    } else {
        throw new Exception(__('Install composer for current work', 'lnw-daily-rewards'));
    }
    if (!is_plugin_active('woocommerce/woocommerce.php')) {
        throw new Exception(__('gatsby-freespin-api should be enabled'));
    }
} catch (Exception $exception) {
    deactivate_plugins( 'coinpipe-wc-near-gateway/index.php');
    @trigger_error(__($exception->getMessage(), 'coinpipe-wc-near-gateway'), E_USER_ERROR);
}

$constructor = Constructor::getInstance();
