<?php
namespace Antavo\LoyaltyApps\Helper\SourceModels;

use Magento\Framework\Option\ArrayInterface;

/**
 *
 */
class JsSDKHashMethodType implements ArrayInterface
{
    /**
     * @var string
     */
    const METHOD_HASH = 'hash';

    /**
     * @var string
     */
    const METHOD_QUERYSTRING = 'query-string';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => 'Hash',
                'value' => self::METHOD_HASH,
            ],
            [
                'label' => 'Query string',
                'value' => self::METHOD_QUERYSTRING,
            ],
        ];
    }
}
