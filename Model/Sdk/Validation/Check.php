<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */

namespace PurchasedAt\Validation;

if (!defined('PHP_INT_MIN')) {
    define('PHP_INT_MIN', ~PHP_INT_MAX);
}

class Check
{
    public static function isIso2Language($s)
    {
        return is_string($s) && strlen($s) == 2;
    }

    public static function isIso3Currency($c)
    {
        return is_string($c) && strlen($c) == 3;
    }

    public static function isIso2Country($c)
    {
        return is_string($c) && strlen($c) == 2;
    }

    public static function isStringNonEmpty($s)
    {
        return is_string($s) && strlen($s) > 0;
    }

    public static function isIntRange($i, $min = PHP_INT_MIN, $max = PHP_INT_MAX)
    {
        return Check::isInt($i) && $i >= $min && $i <= $max;
    }

    public static function isFloat($f)
    {
        $f = (string)$f;
        return (bool)preg_match('/^(\-|\+)?[\d]+(\.[\d]+)$/D', $f);
    }

    public static function isInt($i)
    {
        $i = (string)$i;
        return (bool)preg_match('/^(\-|\+)?[\d]+$/D', $i);
    }

    public static function isUUID($uuid)
    {
        return self::isStringNonEmpty($uuid) && preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/iD', $uuid);
    }
}
