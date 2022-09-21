<?php

namespace CoinPipeWCNearGateway\Model\Constructor;

use CoinPipeWCNearGateway\Controllers\PageConstructor;
use  CoinPipeWCNearGateway\Model\Config;

use  CoinPipeWCNearGateway\Controllers\CurrencyController;
use  CoinPipeWCNearGateway\Controllers\WCNearGateweyController;
use  CoinPipeWCNearGateway\Model\WCNearGateway;

/**
 * Init all main functionality
 *
 * Class Constructor
 * @package  CoinPipeWCNearGateway\Model\Constructor
 */
class Constructor
{
    /**
     * Self Constructor object.
     * @var Constructor $_instance
     */
    private static Constructor $_instance;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * protect singleton  clone
     */
    private function __clone()
    {

    }

    /**
     * Method to use native functions as methods
     *
     * @param $name
     * @param $arguments
     * @return bool|mixed
     */
    public function __call($name, $arguments)
    {
        if (function_exists($name)) {
            return call_user_func_array($name, $arguments);
        }
        return false;
    }

    /**
     * protect singleton __wakeup
     */
    private function __wakeup()
    {

    }

    private function __construct()
    {
        $this->config = new Config();
        $this->setUpActions();
        $this->setUp();
    }

    protected function setUp()
    {

    }


    public function addFrontendStuffs(): void
    {
        $this->initFrontendControllers();
    }

    public function addAdminStuffs(): void
    {
    }

    /**
     * Method to register plugin scripts
     */
    public function addScripts(): void
    {

    }

    protected function initFrontendControllers(): void
    {
        new WCNearGateweyController();
        new CurrencyController();
    }

    /**
     * Method to set up WP actions.
     */
    private function setUpActions(): void
    {
        add_action('init', [&$this, 'registerPostType']);
        add_action('admin_init', [&$this, 'addAdminStuffs']);
        add_action('init', [&$this, 'addFrontendStuffs']);
//        add_action('init', [&$this, 'addScripts']);
        add_action('plugins_loaded', [$this, 'initGatewayClass']);
    }

    public function initGatewayClass()
    {
        new WCNearGateway();
    }

    /**
     * Get self object
     *
     * @return Constructor object
     */
    public static function getInstance(): Constructor
    {
        if (empty(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
}
