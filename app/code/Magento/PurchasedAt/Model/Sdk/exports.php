<?php

/**
 * @Deprecated
 */
function purchasedat_render_script($apiKey, $purchaseOptions, $target = null, $jwtOptions = null)
{
    return \Magento\PurchasedAt\Model\Sdk\PurchaseScript::render($apiKey, $purchaseOptions, $target, $jwtOptions);
}
