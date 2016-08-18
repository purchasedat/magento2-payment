<?php

/**
 * @Deprecated
 */
function purchasedat_render_script($apiKey, $purchaseOptions, $target = null, $jwtOptions = null)
{
    return \PurchasedAt\PurchaseScript::render($apiKey, $purchaseOptions, $target, $jwtOptions);
}
