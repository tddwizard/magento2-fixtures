<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Sales;

use Magento\Sales\Api\Data\ShipmentInterface;

class ShipmentFixturePool
{

    /**
     * @var ShipmentFixture[]
     */
    private $shipmentFixtures = [];

    public function add(ShipmentInterface $shipment, ?string $key = null): void
    {
        if ($key === null) {
            $this->shipmentFixtures[] = new ShipmentFixture($shipment);
        } else {
            $this->shipmentFixtures[$key] = new ShipmentFixture($shipment);
        }
    }

    /**
     * Returns shipment fixture by key, or last added if key not specified
     *
     * @param string|null $key
     * @return ShipmentFixture
     */
    public function get(?string $key = null): ShipmentFixture
    {
        if ($key === null) {
            $key = \array_key_last($this->shipmentFixtures);
        }
        if ($key === null || !array_key_exists($key, $this->shipmentFixtures)) {
            throw new \OutOfBoundsException('No matching shipment found in fixture pool');
        }
        return $this->shipmentFixtures[$key];
    }
}
