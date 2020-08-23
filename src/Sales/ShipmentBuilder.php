<?php

namespace TddWizard\Fixtures\Sales;

use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentTrackCreationInterfaceFactory;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Api\ShipOrderInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Builder to be used by fixtures
 */
class ShipmentBuilder
{
    /**
     * @var ShipmentItemCreationInterfaceFactory
     */
    private $itemFactory;

    /**
     * @var ShipmentTrackCreationInterfaceFactory
     */
    private $trackFactory;

    /**
     * @var ShipOrderInterface
     */
    private $shipOrder;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var OrderInterface
     */
    private $order;

    /**
     * @var int[]
     */
    private $orderItems;

    /**
     * @var string[]
     */
    private $trackingNumbers;

    final public function __construct(
        ShipmentItemCreationInterfaceFactory $itemFactory,
        ShipmentTrackCreationInterfaceFactory $trackFactory,
        ShipOrderInterface $shipOrder,
        ShipmentRepositoryInterface $shipmentRepository,
        OrderInterface $order
    ) {
        $this->itemFactory = $itemFactory;
        $this->trackFactory = $trackFactory;
        $this->shipOrder = $shipOrder;
        $this->shipmentRepository = $shipmentRepository;
        $this->order = $order;

        $this->orderItems = [];
        $this->trackingNumbers = [];
    }

    public static function forOrder(
        OrderInterface $order,
        ObjectManagerInterface $objectManager = null
    ): ShipmentBuilder {
        if ($objectManager === null) {
            $objectManager = Bootstrap::getObjectManager();
        }

        return new static(
            $objectManager->create(ShipmentItemCreationInterfaceFactory::class),
            $objectManager->create(ShipmentTrackCreationInterfaceFactory::class),
            $objectManager->create(ShipOrderInterface::class),
            $objectManager->create(ShipmentRepositoryInterface::class),
            $order
        );
    }

    public function withItem(int $orderItemId, int $qty): ShipmentBuilder
    {
        $builder = clone $this;

        $builder->orderItems[$orderItemId] = $qty;

        return $builder;
    }

    public function withTrackingNumbers(string ...$trackingNumbers): ShipmentBuilder
    {
        $builder = clone $this;

        $builder->trackingNumbers = $trackingNumbers;

        return $builder;
    }

    public function build(): ShipmentInterface
    {
        $shipmentItems = [];

        foreach ($this->orderItems as $orderItemId => $qty) {
            $shipmentItem = $this->itemFactory->create();
            $shipmentItem->setOrderItemId($orderItemId);
            $shipmentItem->setQty($qty);
            $shipmentItems[] = $shipmentItem;
        }

        $tracks = array_map(
            function (string $trackingNumber) {
                $carrierCode = strtok($this->order->getShippingMethod(), '_');
                $track = $this->trackFactory->create();
                $track->setCarrierCode($carrierCode);
                $track->setTitle($carrierCode);
                $track->setTrackNumber($trackingNumber);

                return $track;
            },
            $this->trackingNumbers
        );

        $shipmentId = $this->shipOrder->execute(
            $this->order->getEntityId(),
            $shipmentItems,
            false,
            false,
            null,
            $tracks
        );

        $shipment = $this->shipmentRepository->get($shipmentId);
        if (!empty($this->trackingNumbers)) {
            $shipment->setShippingLabel('%PDF-1.4');
            $this->shipmentRepository->save($shipment);
        }

        return $shipment;
    }
}
