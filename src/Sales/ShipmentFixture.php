<?php

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

    public function getTracks(): array
    {
        return $this->shipment->getTracks();
    }

    public function getShippingLabel(): string
    {
        return (string) $this->shipment->getShippingLabel();
    }
}
