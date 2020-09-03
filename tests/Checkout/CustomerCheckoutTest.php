<?php

namespace TddWizard\Fixtures\Checkout;

use PHPUnit\Framework\TestCase;
use TddWizard\Fixtures\Catalog\ProductBuilder;
use TddWizard\Fixtures\Catalog\ProductFixturePool;
use TddWizard\Fixtures\Customer\AddressBuilder;
use TddWizard\Fixtures\Customer\CustomerBuilder;
use TddWizard\Fixtures\Customer\CustomerFixturePool;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerCheckoutTest extends TestCase
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
            ProductBuilder::aSimpleProduct()->withPrice(10)->build(),
            'simple'
        );
        $this->productFixtures->add(
            ProductBuilder::aVirtualProduct()->withPrice(10)->build(),
            'virtual'
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
    public function testCreateOrderFromCart()
    {
        $this->customerFixtures->get()->login();
        $checkout = CustomerCheckout::fromCart(
            CartBuilder::forCurrentSession()->withSimpleProduct(
                $this->productFixtures->get('simple')->getSku()
            )->build()
        );
        $order = $checkout->placeOrder();
        $this->assertNotEmpty($order->getEntityId(), 'Order should be saved successfully');
        $this->assertNotEmpty($order->getShippingDescription(), 'Order should have a shipping description');
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testCreateOrderFromCartWithVirtualProduct()
    {
        $this->customerFixtures->get()->login();
        $checkout = CustomerCheckout::fromCart(
            CartBuilder::forCurrentSession()->withSimpleProduct(
                $this->productFixtures->get('virtual')->getSku()
            )->build()
        );
        $order = $checkout->placeOrder();
        $this->assertNotEmpty($order->getEntityId(), 'Order should be saved successfully');
        $this->assertEmpty(
            $order->getExtensionAttributes()->getShippingAssignments(),
            'Order with virtual product should not have any shipping assignments'
        );
        $this->assertEmpty($order->getShippingDescription(), 'Order should not have a shipping description');
    }
}
