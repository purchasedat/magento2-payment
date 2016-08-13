<?php

namespace Magento\PurchasedAt\Model\Sdk;

/**
 * @param string                          $apiKey
 * @param \Magento\PurchasedAt\Model\Sdk\PurchaseOptions    $purchaseOptions the purchase options
 * @param string                          $target          id of target dom element
 * @param \Magento\PurchasedAt\Model\Sdk\Signing\JWTOptions $jwtOptions      the JWT options
 *
 * @return string HTML snippet to initialize the purchased.at widget
 * @throws \Exception when invalid data is set on the $purchaseOptions
 */
function renderScript($apiKey, $purchaseOptions, $target = null, $jwtOptions = null)
{
    return PurchaseScript::render($apiKey, $purchaseOptions, $target, $jwtOptions);
}
