<?php

namespace CoinPipeWCNearGateway\Model;

@include_once(WP_CONTENT_DIR . 'plugins/woocommerce/woocommerce.php');
@include_once(WP_CONTENT_DIR . 'plugins/woocommerce/abstract-wc-shipping-method.php');

class WCNearGateway extends \WC_Payment_Gateway
{
    const SERVICE_API = 'https://paywithnear.com/api/v1';

    protected string $clientID;

    public function __construct()
    {
        $options = add_filter('getCoinpipePaymentConfigOptions', [$this, 'getOptions']);
        $clientId = $options['clientId'] ?? '';
        $this->clientID = $clientId;
        $this->setup_meta();
        $this->init_form_fields();
        $this->init_settings();
        $this->setup_actions();
    }

    public function setup_actions(): void
    {
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        add_action('wp_enqueue_scripts', [$this, 'payment_scripts']);
        add_action('woocommerce_api_coinpipe_finish', [$this, 'webhookFinish']);
        add_action('rest_api_init', function () {
            register_rest_route('wc-api', 'coinpipe_payment', array(
                'methods' => 'POST',
                'callback' => [$this, 'webhookPayment'],
                'permission_callback' => '__return_true'
            ));
        });
    }

    public function setup_meta(): void
    {
        $this->id = 'coinpipe_payment';
        $this->icon = site_url('/wp-content/plugins/coinpipe-wc-near-gateway/media/coinpipe.png');
        $this->has_fields = true;
        $this->method_title = 'Coinpipe Payment';
        $this->method_description = 'Coinpipe Payment gateway';
        $this->supports = [
            'products'
        ];
    }

    public function init_form_fields()
    {
        $this->form_fields = [
            'title' => [
                'title' => 'Title',
                'type' => 'text',
                'description' => 'This controls the title which the user sees during checkout.',
                'default' => 'Pay with CoinPipe',
                'desc_tip' => true,
            ],
            'description' => [
                'title' => 'Description',
                'type' => 'textarea',
                'description' => 'This controls the description which the user sees during checkout.',
                'default' => 'Pay with CoinPipe',
            ],
            'client_id' => [
                'title' => 'Client Id',
                'type' => 'text'
            ],
            'secret' => [
                'title' => 'secret',
                'type' => 'text'
            ],
        ];
    }

    /**
     * You will need it if you want your custom credit card form, Step 4 is about it
     */
    public function payment_fields()
    {
        if ($this->settings['description']) {
            echo wpautop($this->settings['description']);
        }
    }

    //@todo place scripts in the new version
    public function payment_scripts(): void
    {
        if (!is_cart() && !is_checkout()) {
            return;
        }
    }

    public function validate_fields(): bool
    {
        //@todo validation fields in the new version
        return true;
    }

    protected function get_amount_currency(array $orderData): string
    {
        $currency = '';
        if (isset($orderData['currency'])) {
            if ($orderData['currency'] == 'USD') {
                $currency = 'amount_usd';
            } elseif ($orderData['currency'] == 'EUR') {
                $currency = 'amount_eur';
            }
        }
        if (!$currency) {
            $currency = 'amount';
        }
        return  $currency;
    }

    protected function getRedirectionLink($order): string
    {
        $params = [];
        $orderData = $order->get_data();
        $currency = $this->get_amount_currency($orderData);
        $params[$currency] = $order->get_total();
        //TESTMODE
        //$params['amount_usd'] = '0.01';
        $params['name'] = 'Order: ' . $order->get_id() . ' ' . site_url();
        $params['return_url'] = site_url("/wc-api/coinpipe_finish/?oid={$order->get_id()}");

        $clientID = $this->settings['client_id'];
        $apiUrl = self::SERVICE_API . "/payment/{$clientID}/page";

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', $apiUrl, [
                \GuzzleHttp\RequestOptions::JSON => $params
            ]);
            if ($response) {
                $data = json_decode((string)$response->getBody());
                $order->update_meta_data('paymentId', $data->paymentID);
                $order->save();
                $order->update_status('on-hold', __("Awaiting payment. Payment ID: {$data->paymentID}", 'coinpipe-wc-near-gateway'));
                return $data->url;
            }

        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            wc_add_notice('Something goes wrong. please try again later', 'error');
        }
        return '';
    }

    public function process_payment($order_id): array
    {
        global $woocommerce;
        $order = wc_get_order($order_id);
        $redirectionLink = $this->getRedirectionLink($order);
        $woocommerce->cart->empty_cart();
        $order->reduce_order_stock();

        if (!$redirectionLink) {
            throw new \Error('Something goes wrong please try again later');
        }
        return [
            'result' => 'success',
            'redirect' => $redirectionLink
        ];
    }


    public function webhookFinish()
    {
        global $woocommerce;
        $orderId = $_GET['oid'];
        $order = wc_get_order($orderId);
        if (!$order) {
            wp_redirect(site_url());
            die;
        }
        wp_redirect($this->get_return_url($order));
        die;
    }


    public function webhookPayment()
    {
        global $woocommerce;
        $rawJson = file_get_contents('php://input');
        $payload = json_decode($rawJson, true);

        $rawSignature = sprintf(
            'Amount=%s;AmountUsd=%s;CurrentDateTime=%s;PaymentID=%s;ReceivedAmount=%s;ReceivedAmountUsd=%s;SecretKey=%s',
            $payload['amount'],
            $payload['amount_usd'],
            $payload['current_datetime'],
            $payload['payment_id'],
            $payload['received_amount'],
            $payload['received_amount_usd'],
            $this->settings['secret'],
        );
        $order = wc_get_orders([
            'limit' => 1,
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_key' => 'paymentId',
            'meta_value' => $payload['payment_id'],
            'meta_compare' => '=',
        ]);
        if ($order || isset($order[0])){
            $computedSignature = hash('sha256', $rawSignature);
            if (!isset($payload['signature']) || $payload['signature'] != $computedSignature) {
                $order[0]->update_status('failed', __("wrong signature from Coinpipe", 'coinpipe-wc-near-gateway'));
                exit;
            }
            $order[0]->update_status('completed', __("completed at ${payload['current_datetime']}", 'coinpipe-wc-near-gateway'));
            exit;
        }
    }

}
