<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Sales;

use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class OrderFixturePoolTest extends TestCase
{
    /**
     * @var OrderFixturePool
     */
    private $orderFixtures;

    protected function setUp(): void
    {
        $this->orderFixtures = new OrderFixturePool();
    }

    public function testLastOrderFixtureReturnedByDefault()
    {
        $firstOrder = $this->createOrder();
        $lastOrder = $this->createOrder();
        $this->orderFixtures->add($firstOrder);
        $this->orderFixtures->add($lastOrder);
        $orderFixture = $this->orderFixtures->get();
        $this->assertEquals($lastOrder->getId(), $orderFixture->getId());
    }

    public function testExceptionThrownWhenAccessingEmptyOrderPool()
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->orderFixtures->get();
    }

    public function testOrderFixtureReturnedByKey()
    {
        $firstOrder = $this->createOrder();
        $lastOrder = $this->createOrder();
        $this->orderFixtures->add($firstOrder, 'first');
        $this->orderFixtures->add($lastOrder, 'last');
        $orderFixture = $this->orderFixtures->get('first');
        $this->assertEquals($firstOrder->getId(), $orderFixture->getId());
    }

    public function testExceptionThrownWhenAccessingNonexistingKey()
    {
        $order = $this->createOrder();
        $this->orderFixtures->add($order, 'foo');
        $this->expectException(\OutOfBoundsException::class);
        $this->orderFixtures->get('bar');
    }

    /**
     * @return Order
     * @throws \Exception
     */
    private function createOrder(): Order
    {
        static $nextId = 1;
        /** @var Order $order */
        $order = Bootstrap::getObjectManager()->create(Order::class);
        $order->setId($nextId++);
        return $order;
    }
}
