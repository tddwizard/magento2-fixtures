<?php

namespace TddWizard\Fixtures\Sales;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use TddWizard\Fixtures\Catalog\ProductBuilder;
use TddWizard\Fixtures\Checkout\CartBuilder;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class ShipmentBuilderTest extends TestCase
{
    /**
     * @var OrderFixture
     */
    private $orderFixture;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shipmentRepository = Bootstrap::getObjectManager()->create(ShipmentRepositoryInterface::class);
    }

    /**
     * @throws LocalizedException
     */
    protected function tearDown(): void
    {
        OrderFixtureRollback::create()->execute($this->orderFixture);

        parent::tearDown();
    }

    /**
     * Create a shipment for all the order's items.
     *
     * @test
     *
     * @throws \Exception
     */
    public function createShipment()
    {
        $order = OrderBuilder::anOrder()->build();
        $this->orderFixture = new OrderFixture($order);

        $shipmentFixture = new ShipmentFixture(ShipmentBuilder::forOrder($order)->build());

        self::assertInstanceOf(ShipmentInterface::class, $this->shipmentRepository->get($shipmentFixture->getId()));
        self::assertEmpty($shipmentFixture->getShippingLabel());
        self::assertEmpty($shipmentFixture->getTracks());
        self::assertFalse($order->canShip());
    }

    /**
     * Create a shipment for all the order's items with tracks and shipping label.
     *
     * @test
     *
     * @throws \Exception
     */
    public function createShipmentWithTracks()
    {
        $order = OrderBuilder::anOrder()->build();
        $this->orderFixture = new OrderFixture($order);

        $shipmentFixture = new ShipmentFixture(
            ShipmentBuilder::forOrder($order)->withTrackingNumbers('123456', '987654', 'abcdef')->build()
        );

        self::assertInstanceOf(ShipmentInterface::class, $this->shipmentRepository->get($shipmentFixture->getId()));

        self::assertNotEmpty($shipmentFixture->getShippingLabel());
        self::assertNotEmpty($shipmentFixture->getTracks());
        self::assertContainsOnlyInstancesOf(ShipmentTrackInterface::class, $shipmentFixture->getTracks());
        self::assertCount(3, $shipmentFixture->getTracks());
        self::assertFalse($order->canShip());
    }

    /**
     * Create a shipment for some of the order's items.
     *
     * @test
     * @throws \Exception
     */
    public function createPartialShipments()
    {
        $order = OrderBuilder::anOrder()->withProducts(
            ProductBuilder::aSimpleProduct()->withSku('foo'),
            ProductBuilder::aSimpleProduct()->withSku('bar')
        )->withCart(
            CartBuilder::forCurrentSession()
                ->withSimpleProduct('foo', 2)
                ->withSimpleProduct('bar', 3)
        )->build();
        $this->orderFixture = new OrderFixture($order);

        $orderItemIds = [];
        /** @var OrderItemInterface $orderItem */
        foreach ($order->getAllVisibleItems() as $orderItem) {
            $orderItemIds[$orderItem->getSku()] = $orderItem->getItemId();
        }

        $shipmentFixture = new ShipmentFixture(
            ShipmentBuilder::forOrder($order)
                ->withItem($orderItemIds['foo'], 2)
                ->withItem($orderItemIds['bar'], 2)
                ->build()
        );

        self::assertInstanceOf(ShipmentInterface::class, $this->shipmentRepository->get($shipmentFixture->getId()));
        self::assertTrue($order->canShip());

        $shipmentFixture = new ShipmentFixture(
            ShipmentBuilder::forOrder($order)
                ->withItem($orderItemIds['bar'], 1)
                ->build()
        );

        self::assertInstanceOf(ShipmentInterface::class, $this->shipmentRepository->get($shipmentFixture->getId()));
        self::assertFalse($order->canShip());
    }
}
