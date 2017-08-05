<?php

namespace TddWizard\Fixtures\Test\Integration;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use TddWizard\Fixtures\Catalog\ProductBuilder;
use TddWizard\Fixtures\Catalog\ProductFixture;
use TddWizard\Fixtures\Catalog\ProductFixtureRollback;
use TddWizard\Fixtures\Checkout\CartBuilder;
use TddWizard\Fixtures\Checkout\CustomerCheckout;
use TddWizard\Fixtures\Customer\AddressBuilder;
use TddWizard\Fixtures\Customer\CustomerBuilder;
use TddWizard\Fixtures\Customer\CustomerFixture;
use TddWizard\Fixtures\Customer\CustomerFixtureRollback;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerCheckoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CustomerFixture
     */
    private $customerFixture;
    /**
     * @var ProductFixture
     */
    private $productFixture;

    /**
     * @var ObjectManager
     */
    private $objectManager;
    /**
     * @var ProductRepositoryInterface\
     */
    private $productRepository;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->customerFixture = new CustomerFixture(
            CustomerBuilder::aCustomer()
                ->withAddresses(
                    AddressBuilder::anAddress()->asDefaultBilling()->asDefaultShipping()
                )
                ->build()
        );
        $this->productFixture = new ProductFixture(
            ProductBuilder::aSimpleProduct()
                ->withPrice(10)
                ->build()
        );
    }
    protected function tearDown()
    {
        CustomerFixtureRollback::create()->execute($this->customerFixture);
        ProductFixtureRollback::create()->execute($this->productFixture);
    }


    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testCreateOrderFromCart()
    {
        $this->customerFixture->login();
        $checkout = CustomerCheckout::fromCart(
            CartBuilder::forCurrentSession()
                ->withSimpleProduct(
                    $this->productFixture->getSku()
                )
                ->build()
        );
        $order = $checkout->placeOrder();
        $this->assertNotEmpty($order->getEntityId(), 'Order should be saved successfully');
        $this->assertNotEmpty($order->getShippingDescription(), 'Order should have a shipping description');
    }

}
