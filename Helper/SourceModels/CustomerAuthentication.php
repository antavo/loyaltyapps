<?php
namespace Antavo\LoyaltyApps\Helper\SourceModels;

use Magento\Framework\Option\ArrayInterface;

/**
 *
 */
class CustomerAuthentication implements ArrayInterface
{
    /**
     * @var string
     */
    const AUTHENTICATION_COOKIE = 'cookie';

    /**
     * @var string
     */
    const AUTHENTICATION_SOCIAL = 'social';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
//            [
//                'label' => self::AUTHENTICATION_COOKIE,
//                'value' => self::AUTHENTICATION_COOKIE,
//            ],
            [
                'label' => self::AUTHENTICATION_SOCIAL,
                'value' => self::AUTHENTICATION_SOCIAL,
            ],
        ];
    }
}
