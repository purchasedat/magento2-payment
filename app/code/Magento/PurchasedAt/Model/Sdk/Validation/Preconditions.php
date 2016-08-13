<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */

namespace Magento\PurchasedAt\Model\Sdk\Validation;

if (!defined('PHP_INT_MIN')) {
    define('PHP_INT_MIN', ~PHP_INT_MAX);
}

class Preconditions
{
    public static function checkIso2Language($s, $name)
    {
        if (!Check::isIso2Language($s)) {
            throw new \InvalidArgumentException('\'' . $name . '\' must be a ISO2 language code but was \'' . var_export($s, true) . '\'');
        }
    }

    public static function checkIso3Currency($c, $name)
    {
        if (!Check::isIso3Currency($c)) {
            throw new \InvalidArgumentException('\'' . $name . '\' must be a ISO3 currency code but was \'' . var_export($c, true) . '\'');
        }
    }

    public static function checkIso2Country($c, $name)
    {
        if (!Check::isIso2Country($c)) {
            throw new \InvalidArgumentException('\'' . $name . '\' must be a ISO2 country code but was \'' . var_export($c, true) . '\'');
        }
    }

    public static function checkStringNonEmpty($s, $name)
    {
        if (!Check::isStringNonEmpty($s)) {
            throw new \InvalidArgumentException('\'' . $name . '\' must be a non-empty string but was \'' . var_export($s, true) . '\'');
        }
    }

    public static function checkFloat($f, $name)
    {
        if (!Check::isFloat($f)) {
            throw new \InvalidArgumentException('\'' . $name . '\' must be a floating point number but was \'' . var_export($f, true) . '\'');
        }
    }

    public static function checkInt($i, $name)
    {
        if (!Check::isInt($i)) {
            throw new \InvalidArgumentException('\'' . $name . '\' must be an integer number but was \'' . var_export($i, true) . '\'');
        }
    }

    public static function checkBool($b, $name)
    {
        if (!is_bool($b)) {
            throw new \InvalidArgumentException('\'' . $name . '\' must be a boolean but was \'' . var_export($b, true) . '\'');
        }
    }

    public static function checkDictionary($d, $name, callable $keyValidator, callable $valueValidator)
    {
        if (!is_array($d)) {
            throw new \InvalidArgumentException('\'' . $name . '\' must be an array but was \'' . var_export($d, true) . '\'');
        }

        foreach ($d as $k => $v) {
            try {
                $keyValidator($k, $name);
            } catch (\Exception $ex) {
                throw new \InvalidArgumentException('keys of ' . $ex->getMessage());
            }
            try {
                $valueValidator($v, $name);
            } catch (\Exception $ex) {
                throw new \InvalidArgumentException('values of ' . $ex->getMessage());
            }
        }
    }

    public static function checkArray($a, $name, callable $validator)
    {
        if (!is_array($a)) {
            throw new \InvalidArgumentException('\'' . $name . '\' must be an array but was \'' . var_export($a, true) . '\'');
        }

        foreach ($a as $v) {
            try {
                $validator($v, $name);
            } catch (\Exception $ex) {
                throw new \InvalidArgumentException('elements of ' . $ex->getMessage());
            }
        }
    }

    public static function checkIntRange($i, $name, $min = PHP_INT_MIN, $max = PHP_INT_MAX)
    {
        if (!Check::isIntRange($i, $min, $max)) {
            if ($min != PHP_INT_MIN && $max != PHP_INT_MAX) {
                throw new \InvalidArgumentException('\'' . $name . '\' must be between [' . $min . ',' . $max . '] but was \'' . var_export($i, true) . '\'');
            } else if ($min != PHP_INT_MIN && $max == PHP_INT_MAX) {
                throw new \InvalidArgumentException('\'' . $name . '\' must be greater than ' . $min . ' but was \'' . var_export($i, true) . '\'');
            } else if ($min == PHP_INT_MIN && $max != PHP_INT_MAX) {
                throw new \InvalidArgumentException('\'' . $name . '\' must be less than ' . $max . ' but was \'' . var_export($i, true) . '\'');
            } else {
                throw new \InvalidArgumentException('\'' . $name . '\' must be a valid integer but was \'' . var_export($i, true) . '\'');
            }
        }
    }

    public static function checkSubclass($o, $name, $cls)
    {
        if (!is_subclass_of($o, $cls, false)) {
            throw new \InvalidArgumentException('\'' . $name . '\' must be of class ' . $cls);
        }
    }

    public static function checkUUID($uuid, $name)
    {
        if (!Check::isUUID($uuid)) {
            throw new \InvalidArgumentException('\'' . $name . '\' must be UUID but was \'' . var_export($uuid, true) . '\'');
        }
    }
}