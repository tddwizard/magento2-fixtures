<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Sales;

use Magento\Sales\Model\Order;

class OrderFixture
{
    /**
     * @var Order
     */
    private $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function getId(): int
    {
        return (int) $this->order->getEntityId();
    }

    public function getCustomerId(): int
    {
        return (int) $this->order->getCustomerId();
    }

    public function getCustomerEmail(): string
    {
        return (string) $this->order->getCustomerEmail();
    }

    /**
     * Obtain `qty_ordered` per order item, indexed with `item_id`.
     *
     * @return float[]
     */
    public function getOrderItemQtys(): array
    {
        $qtys = [];
        foreach ($this->order->getItems() as $item) {
            $qtys[$item->getItemId()] = $item->getQtyOrdered();
        }

        return $qtys;
    }

    public function getPaymentMethod(): string
    {
        return $this->order->getPayment()->getMethod();
    }

    public function getShippingMethod(): string
    {
        return $this->order->getShippingMethod();
    }
}
