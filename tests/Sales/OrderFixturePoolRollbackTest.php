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
class OrderFixturePoolRollbackTest extends TestCase
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    private static $order;
    /**
     * @var OrderFixturePool
     */
    private $orderFixtures;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    public static function setUpBeforeClass(): void
    {
        self::$order = OrderBuilder::anOrder()->build();
    }

    protected function setUp(): void
    {
        $this->orderFixtures = new OrderFixturePool();
        $this->orderRepository = Bootstrap::getObjectManager()->create(OrderRepositoryInterface::class);
    }

    public function testRollbackRemovesOrdersFromPool()
    {
        $this->orderFixtures->add(self::$order);
        $this->orderFixtures->rollback();
        $this->expectException(\OutOfBoundsException::class);
        $this->orderFixtures->get();
    }

    public function testRollbackWorksWithKeys()
    {
        $this->orderFixtures->add(self::$order, 'key');
        $this->orderFixtures->rollback();
        $this->expectException(\OutOfBoundsException::class);
        $this->orderFixtures->get();
    }

    public function testRollbackDeletesOrdersFromDb()
    {
        $this->orderFixtures->add(self::$order);
        $this->orderFixtures->rollback();
        $this->expectException(NoSuchEntityException::class);
        $this->orderRepository->get(self::$order->getId());
    }
}
