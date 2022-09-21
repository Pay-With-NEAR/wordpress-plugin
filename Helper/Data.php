<?php

namespace CoinPipeWCNearGateway\Helper;

class Data
{
    /**
     * Method to clear string
     *
     * @param $string
     * @return string
     */
    public static function clearString($string): string
    {
        return trim(strip_tags($string));
    }

    /**
     * Method to get max array key
     *
     * @param $arr
     * @return int
     */
    public static function getMaxArrKey($arr)
    {
        return (int)max((array_keys($arr)));
    }

    /**
     * Method to clear array
     *
     * @param $arr
     * @return array
     */
    public static function clearArray(array $arr): array
    {
        if (!empty($arr)) {
            foreach ($arr as &$value) {
                if (is_array($value)) {
                    self::clearArray($value);
                } else {
                    $value = self::clearString($value);
                }
            }
        }
        return $arr;
    }

    public static function modifyTemplatesDataForCreation($templates) :array
    {
        $templatesForCreation = [];
        foreach ($templates as $key=>$template) {
            if ($template['file'] && $template['title']) {
                $templatesForCreation[$template['file']] = $template['title'];
            }
        }
        return $templatesForCreation;
    }
}
