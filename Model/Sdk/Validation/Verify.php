<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */

namespace PurchasedAt\Validation;

if (!defined('PHP_INT_MIN')) {
    define('PHP_INT_MIN', ~PHP_INT_MAX);
}

class Verify
{
    public static function verifyStringNonEmpty($s, $name)
    {
        if (!Check::isStringNonEmpty($s)) {
            throw new \LogicException('\'' . $name . '\' must be a non-empty string but was \'' . var_export($s, true) . '\'');
        }
    }

    public static function verifyArrayNonEmpty($a, $name)
    {
        if (!is_array($a) || count($a) < 1) {
            throw new \LogicException('\'' . $name . '\' must have at least one item');
        }
    }

    public static function verifyIntRange($i, $name, $min = PHP_INT_MIN, $max = PHP_INT_MAX)
    {
        if (!Check::isIntRange($i, $min, $max)) {
            if ($min != PHP_INT_MIN && $max != PHP_INT_MAX) {
                throw new \LogicException('\'' . $name . '\' must be between [' . $min . ',' . $max . '] but was \'' . var_export($i, true) . '\'');
            } else if ($min != PHP_INT_MIN && $max == PHP_INT_MAX) {
                throw new \LogicException('\'' . $name . '\' must be greater than ' . $min . ' but was \'' . var_export($i, true) . '\'');
            } else if ($min == PHP_INT_MIN && $max != PHP_INT_MAX) {
                throw new \LogicException('\'' . $name . '\' must be less than ' . $max . ' but was \'' . var_export($i, true) . '\'');
            } else {
                throw new \LogicException('\'' . $name . '\' must be a valid integer but was \'' . var_export($i, true) . '\'');
            }
        }
    }
}

