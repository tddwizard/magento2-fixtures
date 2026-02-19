<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Checkout;

use Magento\Checkout\Model\Cart;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Payment\Model\Config as PaymentConfig;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteManagement;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use TddWizard\Fixtures\Catalog\ProductBuilder;
use TddWizard\Fixtures\Catalog\ProductFixturePool;
use TddWizard\Fixtures\Customer\AddressBuilder;
use TddWizard\Fixtures\Customer\CustomerBuilder;
use TddWizard\Fixtures\Customer\CustomerFixturePool;

/**
 * Regression test for https://github.com/tddwizard/magento2-fixtures/issues/95
 *
 * Ensures the checkout session is cleared even when placeOrder() throws an exception.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerCheckoutSessionClearTest extends TestCase
{
    /**
     * @var CustomerFixturePool
     */
    private $customerFixtures;

    /**
     * @var ProductFixturePool
     */
    private $productFixtures;

    protected function setUp(): void
    {
        $this->productFixtures = new ProductFixturePool();
        $this->customerFixtures = new CustomerFixturePool();
        $this->customerFixtures->add(
            CustomerBuilder::aCustomer()->withAddresses(
                AddressBuilder::anAddress()->asDefaultBilling()->asDefaultShipping()
            )->build()
        );
        $this->productFixtures->add(
            ProductBuilder::aSimpleProduct()->withPrice(10)->build()
        );
    }

    protected function tearDown(): void
    {
        $this->customerFixtures->rollback();
        $this->productFixtures->rollback();
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testCheckoutSessionIsClearedAfterSuccessfulOrder()
    {
        $this->customerFixtures->get()->login();
        $cart = CartBuilder::forCurrentSession()->withSimpleProduct(
            $this->productFixtures->get()->getSku()
        )->build();
        $checkout = CustomerCheckout::fromCart($cart);
        $checkout->placeOrder();

        $this->assertEmpty(
            $cart->getCheckoutSession()->getQuote()->getItemsCount(),
            'Checkout session quote should be cleared after successful order'
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testCheckoutSessionIsClearedWhenSubmitThrowsException()
    {
        $this->customerFixtures->get()->login();
        $cart = CartBuilder::forCurrentSession()->withSimpleProduct(
            $this->productFixtures->get()->getSku()
        )->build();

        $objectManager = Bootstrap::getObjectManager();

        // Create a QuoteManagement mock that throws on submit()
        $quoteManagementMock = $this->getMockBuilder(QuoteManagement::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['submit'])
            ->getMock();
        $quoteManagementMock->method('submit')
            ->willThrowException(new \RuntimeException('Simulated order placement failure'));

        $checkout = new CustomerCheckout(
            $objectManager->create(AddressRepositoryInterface::class),
            $objectManager->create(CartRepositoryInterface::class),
            $quoteManagementMock,
            $objectManager->create(PaymentConfig::class),
            $cart
        );

        $exceptionThrown = false;
        try {
            $checkout->placeOrder();
        } catch (\RuntimeException $e) {
            $exceptionThrown = true;
            $this->assertSame('Simulated order placement failure', $e->getMessage());
        }

        $this->assertTrue($exceptionThrown, 'Expected RuntimeException was not thrown');
        $this->assertEmpty(
            $cart->getCheckoutSession()->getQuote()->getItemsCount(),
            'Checkout session quote should be cleared even when order placement fails (issue #95)'
        );
    }
}
