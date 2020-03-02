<?php

namespace TddWizard\Fixtures\Sales;

use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use TddWizard\Fixtures\Catalog\ProductBuilder;
use TddWizard\Fixtures\Checkout\CartBuilder;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class CreditmemoBuilderTest extends TestCase
{
    /**
     * @var OrderFixture
     */
    private $orderFixture;

    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->creditmemoRepository = Bootstrap::getObjectManager()->create(CreditmemoRepositoryInterface::class);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function tearDown()
    {
        OrderFixtureRollback::create()->execute($this->orderFixture);

        parent::tearDown();
    }

    /**
     * Create a credit memo for all the order's items.
     *
     * @test
     *
     * @throws \Exception
     */
    public function createCreditmemo()
    {
        $order = OrderBuilder::anOrder()->withPaymentMethod('checkmo')->build();
        $this->orderFixture = new OrderFixture($order);

        $refundFixture = new CreditmemoFixture(CreditmemoBuilder::forOrder($order)->build());

        self::assertInstanceOf(CreditmemoInterface::class, $this->creditmemoRepository->get($refundFixture->getId()));
        self::assertFalse($order->canCreditmemo());
    }

    /**
     * Create a credit memo for some of the order's items.
     *
     * @test
     * @throws \Exception
     */
    public function createPartialCreditmemos()
    {
        $order = OrderBuilder::anOrder()->withPaymentMethod('checkmo')->withProducts(
            ProductBuilder::aSimpleProduct()->withSku('foo'),
            ProductBuilder::aSimpleProduct()->withSku('bar')
        )->withCart(
            CartBuilder::forCurrentSession()
                ->withSimpleProduct('foo', 2)
                ->withSimpleProduct('bar', 3)
        )->build();
        $this->orderFixture = new OrderFixture($order);

        $orderItemIds = [];
        /** @var OrderItemInterface $orderItem */
        foreach ($order->getAllVisibleItems() as $orderItem) {
            $orderItemIds[$orderItem->getSku()] = $orderItem->getItemId();
        }

        $refundFixture = new CreditmemoFixture(
            CreditmemoBuilder::forOrder($order)
                ->withItem($orderItemIds['foo'], 2)
                ->withItem($orderItemIds['bar'], 2)
                ->build()
        );

        self::assertInstanceOf(CreditmemoInterface::class, $this->creditmemoRepository->get($refundFixture->getId()));
        self::assertTrue($order->canCreditmemo());

        $refundFixture = new CreditmemoFixture(
            CreditmemoBuilder::forOrder($order)
                ->withItem($orderItemIds['bar'], 1)
                ->build()
        );

        self::assertInstanceOf(CreditmemoInterface::class, $this->creditmemoRepository->get($refundFixture->getId()));
        self::assertFalse($order->canCreditmemo());
    }
}
