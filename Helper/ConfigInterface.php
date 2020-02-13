<?php
namespace Antavo\LoyaltyApps\Helper;

/**
 *
 */
interface ConfigInterface
{
    /**
     * @var string
     */
    const XML_PATH_PLUGIN_ENABLED = 'antavo_loyaltyapps/core/enabled';

    /**
     * @var string
     */
    const XML_PATH_PLUGIN_CUSTOMER_AUTHENTICATION = 'antavo_loyaltyapps/core/authentication';

    /**
     * @var string
     */
    const XML_PATH_LOYALTY_CENTRAL_URL = 'antavo_loyaltyapps/core/loyalty_central_url';

    /**
     * @var string
     */
    const XML_PATH_REGION = 'antavo_loyaltyapps/core/region';

    /**
     * @var string
     */
    const XML_PATH_COUPON_SETTINGS_BOX = 'antavo_loyaltyapps/purchase/coupon_settings_box';

    /**
     * @var string
     */
    const XML_PATH_CHECKOUT_SENDING_TYPE = 'antavo_loyaltyapps/discount/checkout_sending_type';

    /**
     * @var string
     */
    const XML_PATH_CHECKOUT_DISCOUNT_CODE_PREFIX = 'antavo_loyaltyapps/discount/checkout_discount_code_prefix';

    /**
     * @var string
     */
    const XML_PATH_FRIEND_REFERRAL_DISCOUNT_CODE_PREFIX = 'antavo_loyaltyapps/discount/friend_referral_discount_code_prefix';

    /**
     * @var string
     */
    const XML_PATH_FRIEND_REFERRAL_DISCOUNT_WEBSITES = 'antavo_loyaltyapps/discount/friend_referral_discount_websites';

    /**
     * @var string
     */
    const XML_PATH_FRIEND_REFERRAL_DISCOUNT_SEGMENTS = 'antavo_loyaltyapps/discount/friend_referral_discount_segments';

    /**
     * @var string
     */
    const XML_PATH_FRIEND_REFERRAL_DISCOUNT_GROUPS = 'antavo_loyaltyapps/discount/friend_referral_discount_groups';

    /**
     * @var string
     */
    const XML_PATH_SOCIAL_SHARE_INIT = 'antavo_loyaltyapps/socialshare/initialization';

    /**
     * @var string
     */
    const XML_PATH_SOCIAL_SHARE_PRODUCTS = 'antavo_loyaltyapps/socialshare/products';

    /**
     * @var string
     */
    const XML_PATH_SOCIAL_SHARE_ENABLED_STORES = 'antavo_loyaltyapps/socialshare/enabled_stores';

    /**
     * This config value specifies the max amount of spendable points in a
     * single checkout. If set to zero, there will be no upper limit.
     *
     * @var string
     */
    const XML_PATH_POINT_BURNING_LIMIT = 'antavo_loyaltyapps/discount/point_limit';

    /**
     * @var string
     */
    const XML_PATH_AUTO_GENERATE_COUPON = 'antavo_loyaltyapps/purchase/auto_generate_coupon';

    /**
     * @var string
     */
    const XML_PATH_GENERIC_POINTS = 'antavo_loyaltyapps/purchase/generic_points';

    /**
     * @var string
     */
    const XML_PATH_CONVERT_CURRENCY = 'antavo_loyaltyapps/purchase/convert_currency';
    
    /**
     * @var string
     */
    const XML_PATH_POINT_RATE = 'antavo_loyaltyapps/purchase/point_rate';

    /**
     * @var string
     */
    const XML_PATH_POINT_MECHANISM = 'antavo_loyaltyapps/discount/point_mechanism';

    /**
     * @var string
     */
    const XML_PATH_RESERVE_BURNED_POINTS = 'antavo_loyaltyapps/purchase/reserve_burned_points';

    /**
     * @var string
     */
    const XML_PATH_CUSTOMER_OPTIN_EVENT = 'antavo_loyaltyapps/core/opt_in_event';

    /**
     * @var string
     */
    const XML_PATH_CUSTOMER_OPTIN_REDIRECT_URL = 'antavo_loyaltyapps/core/opt_in_redirect_url';

    /**
     * @var string
     */
    const XML_PATH_JS_SDK_HASH_METHOD= 'antavo_loyaltyapps/core/js_sdk_hash_method';

    /**
     * @var string
     */
    const XML_PATH_CHECKOUT_EVENT_SENDING = 'antavo_loyaltyapps/purchase/checkout_event_sending';

    /**
     * @var string
     */
    const XML_PATH_REVIEW_EVENT_SENDING = 'antavo_loyaltyapps/review/enabled';
}
