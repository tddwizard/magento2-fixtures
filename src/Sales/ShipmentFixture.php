<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Sales;

use Magento\Sales\Api\Data\ShipmentInterface;

class ShipmentFixture
{
    /**
     * @var ShipmentInterface
     */
    private $shipment;

    public function __construct(ShipmentInterface $shipment)
    {
        $this->shipment = $shipment;
    }

    public function getId(): int
    {
        return (int) $this->shipment->getEntityId();
    }

    /**
     * @return \Magento\Sales\Api\Data\ShipmentTrackInterface[]
     */
    public function getTracks(): array
    {
        return $this->shipment->getTracks();
    }

    public function getShippingLabel(): string
    {
        return (string) $this->shipment->getShippingLabel();
    }
}
