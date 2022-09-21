<?php

namespace CoinPipeWCNearGateway\Helper;

use CoinPipeWCNearGateway\Model\Config;

class View
{

    protected static $config;

    public static function view($templatePath , $args = null)
    {
        try {
            $error = __('');
            if (!file_exists($templatePath)) {
                throw new \Exception($error);
            }
            $content = require_once ($templatePath);
            if ($content !='') {
                echo $content;
                return true;
            }
            throw new \Exception($error);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        return false;
    }

    /**
     *
     * @return Config
     */
    final protected static function getConfig()
    {
        if (!self::$config) {
            self::$config = new Config();
        }
        return self::$config;
    }

    /**
     * @param $templateName
     * @return mixed|string
     */
    final public static function renderParts($templateName, $data = null)
    {

        $templatePath = self::getConfig()->getTemplatesPath()
            . '/frontend/template-parts/'
            . $templateName;
        try {
            $error = __('');
            if (!file_exists($templatePath)) {
                throw new \Exception($error);
            }
            $content = require($templatePath);
            if ($content != '') {
                return $content;
            }
            throw new \Exception($error);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
