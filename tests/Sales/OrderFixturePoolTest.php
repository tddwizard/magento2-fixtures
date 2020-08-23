<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Sales;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
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
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    protected function setUp(): void
    {
        $this->orderFixtures = new OrderFixturePool();
        $this->orderRepository = Bootstrap::getObjectManager()->create(OrderRepositoryInterface::class);
    }

    public function testLastOrderFixtureReturnedByDefault()
    {
        $firstOrder = OrderBuilder::anOrder()->build();
        $lastOrder = OrderBuilder::anOrder()->build();
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
        $firstOrder = OrderBuilder::anOrder()->build();
        $lastOrder = OrderBuilder::anOrder()->build();
        $this->orderFixtures->add($firstOrder, 'first');
        $this->orderFixtures->add($lastOrder, 'last');
        $orderFixture = $this->orderFixtures->get('first');
        $this->assertEquals($firstOrder->getId(), $orderFixture->getId());
    }

    public function testExceptionThrownWhenAccessingNonexistingKey()
    {
        $order = OrderBuilder::anOrder()->build();
        $this->orderFixtures->add($order, 'foo');
        $this->expectException(\OutOfBoundsException::class);
        $this->orderFixtures->get('bar');
    }

    public function testRollbackRemovesOrdersFromPool()
    {
        $order = OrderBuilder::anOrder()->build();
        $this->orderFixtures->add($order);
        $this->orderFixtures->rollback();
        $this->expectException(\OutOfBoundsException::class);
        $this->orderFixtures->get();
    }
    public function testRollbackDeletesOrdersFromDb()
    {
        $order = OrderBuilder::anOrder()->build();
        $this->orderFixtures->add($order);
        $this->orderFixtures->rollback();
        $this->expectException(NoSuchEntityException::class);
        $this->orderRepository->get($order->getId());
    }
}
