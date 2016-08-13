<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */

namespace Magento\PurchasedAt\Model\Sdk;

class Constants
{

    const SDK_VERSION = '1.2.1';

    const SIGNATURE_HEADER = 'HTTP_X_PAT_SIGNATURE';

    const DEFAULT_WIDGET_URL = 'https://widget.purchased.at/widget/v1/js';
    const DEFAULT_JWT_NOT_BEFORE_INTERVAL = 'PT5M';
    const DEFAULT_JWT_EXPIRATION = '30 minutes';
    const DEFAULT_API_ENDPOINT = 'https://payment.purchased.at';
    const DEFAULT_API_VERIFY_SIGNATURE = true;
    const DEFAULT_API_VERIFY_TIMESTAMP_MAX_AGE = -1;

    const NOTIFICATION_OK_RESULT = '"OK"';
}
