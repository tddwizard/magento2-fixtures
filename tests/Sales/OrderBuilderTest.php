<?php

namespace TddWizard\Fixtures\Sales;

use Magento\Framework\Exception\LocalizedException;
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
     * @var OrderFixture[]
     */
    private $orderFixtures;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepository = Bootstrap::getObjectManager()->create(OrderRepositoryInterface::class);
    }

    /**
     * @throws LocalizedException
     */
    protected function tearDown(): void
    {
        OrderFixtureRollback::create()->execute(...$this->orderFixtures);

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
        $orderFixture = new OrderFixture(
            OrderBuilder::anOrder()->build()
        );
        $this->orderFixtures[] = $orderFixture;

        self::assertInstanceOf(OrderInterface::class, $this->orderRepository->get($orderFixture->getId()));
        self::assertNotEmpty($orderFixture->getOrderItemQtys());
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
        $orderFixture = new OrderFixture(
            OrderBuilder::anOrder()->withProducts(ProductBuilder::aSimpleProduct())->build()
        );
        $this->orderFixtures[] = $orderFixture;

        self::assertInstanceOf(OrderInterface::class, $this->orderRepository->get($orderFixture->getId()));
        self::assertCount(1, $orderFixture->getOrderItemQtys());
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
        $orderFixture = new OrderFixture(
            OrderBuilder::anOrder()->withProducts(
                ProductBuilder::aSimpleProduct()->withSku('foo'),
                ProductBuilder::aSimpleProduct()->withSku('bar')
            )->build()
        );
        $this->orderFixtures[] = $orderFixture;

        self::assertInstanceOf(OrderInterface::class, $this->orderRepository->get($orderFixture->getId()));
        self::assertCount(2, $orderFixture->getOrderItemQtys());
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

        $orderFixture = new OrderFixture(
            OrderBuilder::anOrder()->withCustomer($customerBuilder)->build()
        );
        $this->orderFixtures[] = $orderFixture;

        self::assertInstanceOf(OrderInterface::class, $this->orderRepository->get($orderFixture->getId()));
        self::assertSame($customerEmail, $orderFixture->getCustomerEmail());
        self::assertNotEmpty($orderFixture->getOrderItemQtys());
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

        $orderFixture = new OrderFixture(
            OrderBuilder::anOrder()
                ->withProducts(...$productBuilders)
                ->withCustomer($customerBuilder)
                ->withCart($cartBuilder)
                ->withPaymentMethod($paymentMethod)->withShippingMethod($shippingMethod)
                ->build()
        );
        $this->orderFixtures[] = $orderFixture;

        self::assertInstanceOf(OrderInterface::class, $this->orderRepository->get($orderFixture->getId()));
        self::assertSame($customerEmail, $orderFixture->getCustomerEmail());
        self::assertEmpty(array_diff($cartItems, $orderFixture->getOrderItemQtys()));
        self::assertSame($paymentMethod, $orderFixture->getPaymentMethod());
        self::assertSame($shippingMethod, $orderFixture->getShippingMethod());
    }

    /**
     * Create multiple orders. Assert all of them were successfully built.
     *
     * @test
     * @throws \Exception
     */
    public function createMultipleOrders()
    {
        $shippingMethod = 'flatrate_flatrate';

        // first order, simple
        $orderFixture = new OrderFixture(
            OrderBuilder::anOrder()
                ->withShippingMethod($shippingMethod)
                ->build()
        );
        $this->orderFixtures[] = $orderFixture;

        // second order, with specified cart
        $cartBuilder = CartBuilder::forCurrentSession();
        $orderWithCartFixture = new OrderFixture(
            OrderBuilder::anOrder()
                ->withShippingMethod($shippingMethod)
                ->withProducts(ProductBuilder::aSimpleProduct()->withSku('bar'))
                ->withCart($cartBuilder->withSimpleProduct('bar', 3))
                ->build()
        );
        $this->orderFixtures[] = $orderWithCartFixture;

        // third order, with specified customer
        $orderWithCustomerFixture = new OrderFixture(
            OrderBuilder::anOrder()
                ->withShippingMethod($shippingMethod)
                ->withCustomer(
                    CustomerBuilder::aCustomer()
                        ->withAddresses(
                            AddressBuilder::anAddress(null, 'de_AT')
                                ->asDefaultBilling()
                                ->asDefaultShipping()
                        )
                )
                ->build()
        );
        $this->orderFixtures[] = $orderWithCustomerFixture;

        // assert all fixtures were created with separate customers.
        self::assertCount(3, $this->orderFixtures);
        self::assertContainsOnlyInstancesOf(OrderFixture::class, $this->orderFixtures);

        $customerIds[$orderFixture->getCustomerId()] = 1;
        $customerIds[$orderWithCartFixture->getCustomerId()] = 1;
        $customerIds[$orderWithCustomerFixture->getCustomerId()] = 1;
        self::assertCount(3, $customerIds);
    }

    /**
     * Create orders for faker addresses with either state or province. Assert both types have a `region_id` assigned.
     *
     * @test
     * @throws \Exception
     */
    public function createIntlOrders()
    {
        $atLocale = 'de_AT';
        $atOrder = OrderBuilder::anOrder()
            ->withCustomer(
                CustomerBuilder::aCustomer()->withAddresses(
                    AddressBuilder::anAddress(null, $atLocale)->asDefaultBilling()->asDefaultShipping()
                )
            )
            ->build();
        $this->orderFixtures[] = new OrderFixture($atOrder);

        $usLocale = 'en_US';
        $usOrder = OrderBuilder::anOrder()
            ->withCustomer(
                CustomerBuilder::aCustomer()->withAddresses(
                    AddressBuilder::anAddress(null, $usLocale)->asDefaultBilling()->asDefaultShipping()
                )
            )
            ->build();
        $this->orderFixtures[] = new OrderFixture($usOrder);

        $caLocale = 'en_CA';
        $caOrder = OrderBuilder::anOrder()
            ->withCustomer(
                CustomerBuilder::aCustomer()->withAddresses(
                    AddressBuilder::anAddress(null, $caLocale)->asDefaultBilling()->asDefaultShipping()
                )
            )
            ->build();
        $this->orderFixtures[] = new OrderFixture($caOrder);

        self::assertSame(substr($atLocale, 3, 4), $atOrder->getBillingAddress()->getCountryId());
        self::assertNotEmpty($atOrder->getBillingAddress()->getRegionId());
        self::assertSame(substr($usLocale, 3, 4), $usOrder->getBillingAddress()->getCountryId());
        self::assertNotEmpty($usOrder->getBillingAddress()->getRegionId());
        self::assertSame(substr($caLocale, 3, 4), $caOrder->getBillingAddress()->getCountryId());
        self::assertNotEmpty($caOrder->getBillingAddress()->getRegionId());
    }
}
