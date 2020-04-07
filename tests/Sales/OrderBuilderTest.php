<?php

namespace TddWizard\Fixtures\Sales;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use TddWizard\Fixtures\Catalog\ProductBuilder;
use TddWizard\Fixtures\Checkout\CartBuilder;
use TddWizard\Fixtures\Customer\AddressBuilder;
use TddWizard\Fixtures\Customer\CustomerBuilder;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class OrderBuilderTest extends TestCase
{
    /**
     * @var OrderFixture
     */
    private $orderFixture;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->orderRepository = Bootstrap::getObjectManager()->create(OrderRepositoryInterface::class);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function tearDown()
    {
        OrderFixtureRollback::create()->execute($this->orderFixture);

        parent::tearDown();
    }

    /**
     * Create an order for an internally generated customer and internally generated product(s).
     *
     * Easy to set up, least flexible.
     *
     * @test
     * @throws \Exception
     */
    public function createOrder()
    {
        $this->orderFixture = new OrderFixture(
            OrderBuilder::anOrder()->build()
        );

        self::assertInstanceOf(OrderInterface::class, $this->orderRepository->get($this->orderFixture->getId()));
        self::assertNotEmpty($this->orderFixture->getOrderItemQtys());
    }

    /**
     * Create an order for an internally generated customer.
     *
     * Control the product included with the order, use random item quantities.
     *
     * @test
     * @throws \Exception
     */
    public function createOrderWithProduct()
    {
        $this->orderFixture = new OrderFixture(
            OrderBuilder::anOrder()->withProducts(ProductBuilder::aSimpleProduct())->build()
        );

        self::assertInstanceOf(OrderInterface::class, $this->orderRepository->get($this->orderFixture->getId()));
        self::assertCount(1, $this->orderFixture->getOrderItemQtys());
    }

    /**
     * Create an order for an internally generated customer with multiple products.
     *
     * Control the products included with the order, use random item quantities.
     *
     * @test
     * @throws \Exception
     */
    public function createOrderWithProducts()
    {
        $this->orderFixture = new OrderFixture(
            OrderBuilder::anOrder()->withProducts(
                ProductBuilder::aSimpleProduct()->withSku('foo'),
                ProductBuilder::aSimpleProduct()->withSku('bar')
            )->build()
        );

        self::assertInstanceOf(OrderInterface::class, $this->orderRepository->get($this->orderFixture->getId()));
        self::assertCount(2, $this->orderFixture->getOrderItemQtys());
    }

    /**
     * Create an order for a given customer with internally generated product(s).
     *
     * Control the customer placing the order.
     *
     * @test
     * @throws \Exception
     */
    public function createOrderWithCustomer()
    {
        $customerEmail = 'test@example.com';
        $customerBuilder = CustomerBuilder::aCustomer()
            ->withEmail($customerEmail)
            ->withAddresses(AddressBuilder::anAddress()->asDefaultBilling()->asDefaultShipping());

        $this->orderFixture = new OrderFixture(
            OrderBuilder::anOrder()->withCustomer($customerBuilder)->build()
        );

        self::assertInstanceOf(OrderInterface::class, $this->orderRepository->get($this->orderFixture->getId()));
        self::assertSame($customerEmail, $this->orderFixture->getCustomerEmail());
        self::assertNotEmpty($this->orderFixture->getOrderItemQtys());
    }

    /**
     * Create an order for a given cart.
     *
     * Complex to set up, most flexible:
     * - define products
     * - define customer
     * - set item quantities
     * - set payment and shipping method
     *
     * @test
     * @throws \Exception
     */
    public function createOrderWithCart()
    {
        $cartItems = ['foo' => 2, 'bar' => 3];
        $customerEmail = 'test@example.com';
        $paymentMethod = 'checkmo';
        $shippingMethod = 'flatrate_flatrate';

        $productBuilders = [];
        foreach ($cartItems as $sku => $qty) {
            $productBuilders[] = ProductBuilder::aSimpleProduct()->withSku($sku);
        }

        $customerBuilder = CustomerBuilder::aCustomer();
        $customerBuilder = $customerBuilder
            ->withEmail($customerEmail)
            ->withAddresses(AddressBuilder::anAddress()->asDefaultBilling()->asDefaultShipping());

        $cartBuilder = CartBuilder::forCurrentSession();
        foreach ($cartItems as $sku => $qty) {
            $cartBuilder = $cartBuilder->withSimpleProduct($sku, $qty);
        }

        $this->orderFixture = new OrderFixture(
            OrderBuilder::anOrder()
                ->withProducts(...$productBuilders)
                ->withCustomer($customerBuilder)
                ->withCart($cartBuilder)
                ->withPaymentMethod($paymentMethod)->withShippingMethod($shippingMethod)
                ->build()
        );

        self::assertInstanceOf(OrderInterface::class, $this->orderRepository->get($this->orderFixture->getId()));
        self::assertSame($customerEmail, $this->orderFixture->getCustomerEmail());
        self::assertEmpty(array_diff($cartItems, $this->orderFixture->getOrderItemQtys()));
        self::assertSame($paymentMethod, $this->orderFixture->getPaymentMethod());
        self::assertSame($shippingMethod, $this->orderFixture->getShippingMethod());
    }
}
