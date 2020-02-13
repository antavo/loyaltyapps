<?php
namespace Antavo\LoyaltyApps\Test\Unit\Helper;

use Antavo\LoyaltyApps\Helper\Coupon;
use Antavo\LoyaltyApps\Test\Unit\TestCase;

/**
 *
 */
class CouponTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_couponModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_ruleModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_mathRandomHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_dateTimeHelper;

    /**
     * @inheritdoc
     */
    protected function getClass()
    {
        return Coupon::class;
    }

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->_scopeConfig = $this
            ->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_couponModel = $this
            ->getMockBuilder(\Magento\SalesRule\Model\Coupon::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_ruleModel = $this
            ->getMockBuilder(\Magento\SalesRule\Model\Rule::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mathRandomHelper = $this
            ->getMockBuilder(\Magento\Framework\Math\Random::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_dateTimeHelper = $this
            ->getMockBuilder(\Magento\Framework\Stdlib\DateTime\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Coupon::getCheckoutCouponCodePrefix()
     */
    public function testGetCheckoutCouponCodePrefix_default()
    {
        $this->_scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(NULL);

        /** @var \Antavo\LoyaltyApps\Helper\Coupon $class */
        $class = $this->getClassMock(
            [
                'scopeConfig' => $this->_scopeConfig,
            ]
        );

        $this->assertEquals(
            Coupon::DEFAULT_COUPON_CODE_PREFIX,
            $class->getCheckoutCouponCodePrefix()
        );
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Coupon::getCheckoutCouponCodePrefix()
     */
    public function testGetCheckoutCouponCodePrefix_set()
    {
        $this->_scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->willReturn('TESTPREFIX');

        /** @var \Antavo\LoyaltyApps\Helper\Coupon $class */
        $class = $this->getClassMock(
            [
                'scopeConfig' => $this->_scopeConfig,
            ]
        );

        $this->assertEquals(
            'TESTPREFIX',
            $class->getCheckoutCouponCodePrefix()
        );
    }

    /**
     * @return array
     */
    public function isPointsBurningCouponCodeProvider()
    {
        return [
            'default prefix #1' => [
                NULL,
                'ANTPB-2-181106-WQEYXA',
                TRUE,
            ],
            'default prefix #2' => [
                NULL,
                'NOPE-2-181106-WQEYXA',
                FALSE,
            ],
            'custom prefix #1' => [
                'TESTPREFIX',
                'TESTPREFIX-2-181106-WQEYXA',
                TRUE,
            ],
            'custom prefix #2' => [
                'TESTPREFIX',
                'ANTPB-2-181106-WQEYXA',
                FALSE,
            ],
        ];
    }

    /**
     * @param string $prefix
     * @param string $code
     * @param bool $expected
     * @dataProvider isPointsBurningCouponCodeProvider
     * @covers \Antavo\LoyaltyApps\Helper\Coupon::isPointBurningCouponCode()
     */
    public function testIsPointsBurningCouponCode($prefix, $code, $expected)
    {
        $this->_scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->willReturn($prefix);

        /** @var \Antavo\LoyaltyApps\Helper\Coupon $class */
        $class = $this->getClassMock(
            [
                'scopeConfig' => $this->_scopeConfig,
            ]
        );

        $this->assertEquals($expected, $class->isPointBurningCouponCode($code));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Coupon::generatePointBurningCouponCode()
     */
    public function testGeneratePointBurningCouponCode()
    {
        $this->_scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(NULL);
        $this->_dateTimeHelper
            ->expects($this->once())
            ->method('gmtDate')
            ->willReturn('20181106');
        $this->_mathRandomHelper
            ->expects($this->once())
            ->method('getRandomString')
            ->willReturn('ABCDE');

        /** @var \Antavo\LoyaltyApps\Helper\Coupon $class */
        $class = $this->getClassMock(
            [
                'scopeConfig' => $this->_scopeConfig,
                'dateTimeHelper' => $this->_dateTimeHelper,
                'mathRandomHelper' => $this->_mathRandomHelper,
            ]
        );

        $this->assertEquals(
            'ANTPB-100-20181106-ABCDE',
            $class->generatePointBurningCouponCode('100')
        );
    }

    /**
     * @return array
     */
    public function calculatePointsBurnedProvider()
    {
        return [
            [100, 1, 100],
            [100, 0.5, 50],
            [100, 0.59, 50],
            [100, 0.51, 50],
        ];
    }

    /**
     * @param float $value
     * @param float $rate
     * @param float $expected
     * @dataProvider calculatePointsBurnedProvider
     * @covers \Antavo\LoyaltyApps\Helper\Coupon::calculatePointsBurned()
     */
    public function calculatePointsBurned($value, $rate, $expected)
    {
        $this->_scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->willReturn($rate);

        /** @var \Antavo\LoyaltyApps\Helper\Coupon $class */
        $class = $this->getClassMock(
            [
                'scopeConfig' => $this->_scopeConfig,
            ]
        );

        $this->assertEquals($expected, $class->calculatePointsBurned($value));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Coupon::deletePointBurningCoupon()
     */
    public function testDeletePointBurningCoupon_invalid_coupon()
    {
        /** @var \Antavo\LoyaltyApps\Helper\Coupon $class */
        $class = $this->getClassMock(
            [
                'scopeConfig' => $this->_scopeConfig,
            ]
        );

        // coupon is not point burning coupon
        $this->assertFalse($class->deletePointBurningCoupon('something-code'));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Coupon::deletePointBurningCoupon()
     */
    public function testDeletePointBurningCoupon_valid_coupon_no_id()
    {
        $this->_couponModel
            ->expects($this->once())
            ->method('load')
            ->willReturn($this->_couponModel);

        /** @var \Antavo\LoyaltyApps\Helper\Coupon $class */
        $class = $this->getClassMock(
            [
                'scopeConfig' => $this->_scopeConfig,
                'couponModel' => $this->_couponModel,
            ]
        );

        $this->assertTrue($class->deletePointBurningCoupon('ANTPB-2-181106-WQEYXA'));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Coupon::deletePointBurningCoupon()
     */
    public function testDeletePointBurningCoupon_valid_coupon_no_rule_id()
    {
        $this->_couponModel
            ->expects($this->once())
            ->method('getId')
            ->willReturn(3);

        $this->_couponModel
            ->expects($this->once())
            ->method('load')
            ->willReturn($this->_couponModel);

        $this->_ruleModel
            ->expects($this->any())
            ->method('load')
            ->willReturn($this->_ruleModel);

        /** @var \Antavo\LoyaltyApps\Helper\Coupon $class */
        $class = $this->getClassMock(
            [
                'scopeConfig' => $this->_scopeConfig,
                'couponModel' => $this->_couponModel,
                'ruleModel' => $this->_ruleModel,
            ]
        );

        // no rule id
        $this->assertTrue($class->deletePointBurningCoupon('ANTPB-2-181106-WQEYXA'));
    }

    /**
     * @covers \Antavo\LoyaltyApps\Helper\Coupon::deletePointBurningCoupon()
     */
    public function testDeletePointBurningCoupon()
    {
        $this->_couponModel
            ->expects($this->once())
            ->method('getId')
            ->willReturn('3');

        $this->_couponModel
            ->expects($this->once())
            ->method('load')
            ->willReturn($this->_couponModel);

        $this->_ruleModel
            ->expects($this->once())
            ->method('getId')
            ->willReturn('404');

        $this->_ruleModel
            ->expects($this->any())
            ->method('load')
            ->willReturn($this->_ruleModel);

        $this->_ruleModel
            ->expects($this->atLeastOnce())
            ->method('delete');

        /** @var \Antavo\LoyaltyApps\Helper\Coupon $class */
        $class = $this->getClassMock(
            [
                'scopeConfig' => $this->_scopeConfig,
                'couponModel' => $this->_couponModel,
                'ruleModel' => $this->_ruleModel,
            ]
        );

        // test fails if rule delete wasn't invoked
        $class->deletePointBurningCoupon('ANTPB-2-181106-WQEYXA');
    }
}
