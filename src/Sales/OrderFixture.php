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

    public function getOrder(): OrderInterface
    {
        return $this->order;
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
            $qtys[$item->getItemId()] = (float)$item->getQtyOrdered();
        }

        return $qtys;
    }

    public function getPaymentMethod(): string
    {
        $payment = $this->order->getPayment();
        if ($payment === null) {
            throw new \RuntimeException('Order does not have any payment information');
        }
        return (string)$payment->getMethod();
    }

    public function getShippingMethod(): string
    {
        return (string)$this->order->getShippingMethod();
    }

    public function rollback(): void
    {
        OrderFixtureRollback::create()->execute($this);
    }
}
