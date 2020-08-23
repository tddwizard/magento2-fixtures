<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Sales;

use Magento\Sales\Api\Data\OrderInterface;

class OrderFixturePool
{

    /**
     * @var OrderFixture[]
     */
    private $orderFixtures = [];

    public function add(OrderInterface $order, string $key = null): void
    {
        if ($key === null) {
            $this->orderFixtures[] = new OrderFixture($order);
        } else {
            $this->orderFixtures[$key] = new OrderFixture($order);
        }
    }

    /**
     * Returns order fixture by key, or last added if key not specified
     *
     * @param string|null $key
     * @return OrderFixture
     */
    public function get(string $key = null): OrderFixture
    {
        if ($key === null) {
            $key = \array_key_last($this->orderFixtures);
        }
        if (!array_key_exists($key, $this->orderFixtures)) {
            throw new \OutOfBoundsException('No matching order found in fixture pool');
        }
        return $this->orderFixtures[$key];
    }

    public function rollback(): void
    {
        OrderFixtureRollback::create()->execute(...$this->orderFixtures);
        $this->orderFixtures = [];
    }
}
