<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper\SourceModels;

use Antavo\LoyaltyApps\Helper\SourceModels\CheckoutSendingType;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class CheckoutSendingTypeTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return CheckoutSendingType::class;
    }

    /**
     * @coversNothing
     */
    public function testInheritance()
    {
        $this->assertInstanceOf(
            'Magento\Framework\Option\ArrayInterface',
            $this->_class
        );
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\SourceModels\CheckoutSendingType::toOptionArray()
     */
    public function testToOptionArray()
    {
        $this->assertEquals(
            [
                [
                    'label' => 'Payment received',
                    'value' => CheckoutSendingType::TYPE_PAYMENT_RECEIVED,
                ],
                [
                    'label' => 'Purchase completed',
                    'value' => CheckoutSendingType::TYPE_PURCHASE_COMPLETED,
                ],
            ],
            (new CheckoutSendingType)->toOptionArray()
        );
    }
}
