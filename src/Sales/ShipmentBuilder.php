<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Sales;

use Magento\Sales\Api\Data\ShipmentCreationArgumentsExtensionFactory;
use Magento\Sales\Api\Data\ShipmentCreationArgumentsExtensionInterface;
use Magento\Sales\Api\Data\ShipmentCreationArgumentsInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentTrackCreationInterface;
use Magento\Sales\Api\Data\ShipmentTrackCreationInterfaceFactory;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Api\ShipOrderInterface;
use Magento\Sales\Model\Order;
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
     * @var Order
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
    /**
     * @var string
     */
    private $sourceCode;
    /**
     * @var ShipmentCreationArgumentsExtensionFactory
     */
    private $shipmentCreationArgumentsExtensionFactory;

    final public function __construct(
        ShipmentItemCreationInterfaceFactory $itemFactory,
        ShipmentTrackCreationInterfaceFactory $trackFactory,
        ShipmentCreationArgumentsExtensionFactory $shipmentCreationArgumentsExtensionFactory,
        ShipOrderInterface $shipOrder,
        ShipmentRepositoryInterface $shipmentRepository,
        Order $order
    ) {
        $this->itemFactory = $itemFactory;
        $this->trackFactory = $trackFactory;
        $this->shipmentCreationArgumentsExtensionFactory = $shipmentCreationArgumentsExtensionFactory;
        $this->shipOrder = $shipOrder;
        $this->shipmentRepository = $shipmentRepository;
        $this->order = $order;

        $this->orderItems = [];
        $this->trackingNumbers = [];
    }

    public static function forOrder(
        Order $order
    ): ShipmentBuilder {
        $objectManager = Bootstrap::getObjectManager();

        return new static(
            $objectManager->create(ShipmentItemCreationInterfaceFactory::class),
            $objectManager->create(ShipmentTrackCreationInterfaceFactory::class),
            $objectManager->create(ShipmentCreationArgumentsExtensionFactory::class),
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

    public function withSource(string $sourceCode): ShipmentBuilder
    {
        $builder = clone $this;

        $builder->sourceCode = $sourceCode;

        return $builder;
    }

    public function build(): ShipmentInterface
    {
        $shipmentItems = $this->buildShipmentItems();
        $tracks = $this->buildTracks();
        $arguments = $this->buildArguments();

        $shipmentId = $this->shipOrder->execute(
            $this->order->getEntityId(),
            $shipmentItems,
            false,
            false,
            null,
            $tracks,
            [],
            $arguments
        );

        $shipment = $this->shipmentRepository->get($shipmentId);
        if (!empty($this->trackingNumbers)) {
            $shipment->setShippingLabel('%PDF-1.4');
            $this->shipmentRepository->save($shipment);
        }

        return $shipment;
    }

    /**
     * @return \Magento\Sales\Api\Data\ShipmentTrackCreationInterface[]
     */
    private function buildTracks(): array
    {
        return array_map(
            function (string $trackingNumber): ShipmentTrackCreationInterface {
                $carrierCode = strtok((string)$this->order->getShippingMethod(), '_');
                $track = $this->trackFactory->create();
                $track->setCarrierCode($carrierCode);
                $track->setTitle($carrierCode);
                $track->setTrackNumber($trackingNumber);

                return $track;
            },
            $this->trackingNumbers
        );
    }

    private function buildArguments(): ShipmentCreationArgumentsInterface
    {
        /** @var ShipmentCreationArgumentsInterface $arguments */
        $arguments = Bootstrap::getObjectManager()->create(ShipmentCreationArgumentsInterface::class);
        if (isset($this->sourceCode)) {
            $arguments->setExtensionAttributes($this->shipmentCreationArgumentsExtensionFactory->create());
            $arguments->getExtensionAttributes()->setSourceCode($this->sourceCode);
        }
        return $arguments;
    }

    /**
     * @return \Magento\Sales\Api\Data\ShipmentItemCreationInterface[]
     */
    private function buildShipmentItems(): array
    {
        $shipmentItems = [];

        foreach ($this->orderItems as $orderItemId => $qty) {
            $shipmentItem = $this->itemFactory->create();
            $shipmentItem->setOrderItemId($orderItemId);
            $shipmentItem->setQty($qty);
            $shipmentItems[] = $shipmentItem;
        }
        return $shipmentItems;
    }
}
