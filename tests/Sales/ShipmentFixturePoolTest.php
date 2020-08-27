<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Sales;

use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class ShipmentFixturePoolTest extends TestCase
{
    /**
     * @var ShipmentFixturePool
     */
    private $shipmentFixtures;
    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    protected function setUp(): void
    {
        $this->shipmentFixtures = new ShipmentFixturePool();
        $this->shipmentRepository = Bootstrap::getObjectManager()->create(ShipmentRepositoryInterface::class);
    }

    public function testLastShipmentFixtureReturnedByDefault()
    {
        $firstShipment = $this->createShipment();
        $lastShipment = $this->createShipment();
        $this->shipmentFixtures->add($firstShipment);
        $this->shipmentFixtures->add($lastShipment);
        $shipmentFixture = $this->shipmentFixtures->get();
        $this->assertEquals($lastShipment->getEntityId(), $shipmentFixture->getId());
    }

    public function testExceptionThrownWhenAccessingEmptyShipmentPool()
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->shipmentFixtures->get();
    }

    public function testShipmentFixtureReturnedByKey()
    {
        $firstShipment = $this->createShipment();
        $lastShipment = $this->createShipment();
        $this->shipmentFixtures->add($firstShipment, 'first');
        $this->shipmentFixtures->add($lastShipment, 'last');
        $shipmentFixture = $this->shipmentFixtures->get('first');
        $this->assertEquals($firstShipment->getEntityId(), $shipmentFixture->getId());
    }

    public function testExceptionThrownWhenAccessingNonexistingKey()
    {
        $shipment = $this->createShipment();
        $this->shipmentFixtures->add($shipment, 'foo');
        $this->expectException(\OutOfBoundsException::class);
        $this->shipmentFixtures->get('bar');
    }

    /**
     * @return ShipmentInterface
     * @throws \Exception
     */
    private function createShipment(): ShipmentInterface
    {
        static $nextId = 1;
        /** @var ShipmentInterface $shipment */
        $shipment = Bootstrap::getObjectManager()->create(ShipmentInterface::class);
        $shipment->setEntityId($nextId++);
        return $shipment;
    }
}
