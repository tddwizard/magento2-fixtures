<?php

namespace TddWizard\Fixtures\Sales;

use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceItemCreationInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\InvoiceOrderInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Builder to be used by fixtures
 */
class InvoiceBuilder
{
    /**
     * @var InvoiceItemCreationInterfaceFactory
     */
    private $itemFactory;

    /**
     * @var InvoiceOrderInterface
     */
    private $invoiceOrder;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var OrderInterface
     */
    private $order;

    /**
     * @var int[]
     */
    private $orderItems;

    final public function __construct(
        InvoiceItemCreationInterfaceFactory $itemFactory,
        InvoiceOrderInterface $invoiceOrder,
        InvoiceRepositoryInterface $invoiceRepository,
        OrderInterface $order
    ) {
        $this->itemFactory = $itemFactory;
        $this->invoiceOrder = $invoiceOrder;
        $this->invoiceRepository = $invoiceRepository;
        $this->order = $order;

        $this->orderItems = [];
    }

    public static function forOrder(
        OrderInterface $order,
        ObjectManagerInterface $objectManager = null
    ): InvoiceBuilder {
        if ($objectManager === null) {
            $objectManager = Bootstrap::getObjectManager();
        }

        return new static(
            $objectManager->create(InvoiceItemCreationInterfaceFactory::class),
            $objectManager->create(InvoiceOrderInterface::class),
            $objectManager->create(InvoiceRepositoryInterface::class),
            $order
        );
    }

    public function withItem(int $orderItemId, int $qty): InvoiceBuilder
    {
        $builder = clone $this;

        $builder->orderItems[$orderItemId] = $qty;

        return $builder;
    }

    public function build(): InvoiceInterface
    {
        $invoiceItems = [];

        foreach ($this->orderItems as $orderItemId => $qty) {
            $invoiceItem = $this->itemFactory->create();
            $invoiceItem->setOrderItemId($orderItemId);
            $invoiceItem->setQty($qty);
            $invoiceItems[] = $invoiceItem;
        }

        $invoiceId = $this->invoiceOrder->execute($this->order->getEntityId(), false, $invoiceItems);

        return $this->invoiceRepository->get($invoiceId);
    }
}
