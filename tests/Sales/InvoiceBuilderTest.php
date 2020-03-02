<?php

namespace TddWizard\Fixtures\Sales;

use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use TddWizard\Fixtures\Catalog\ProductBuilder;
use TddWizard\Fixtures\Checkout\CartBuilder;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class InvoiceBuilderTest extends TestCase
{
    /**
     * @var OrderFixture
     */
    private $orderFixture;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->invoiceRepository = Bootstrap::getObjectManager()->create(InvoiceRepositoryInterface::class);
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
     * Create a invoice for all the order's items.
     *
     * @test
     *
     * @throws \Exception
     */
    public function createInvoice()
    {
        $order = OrderBuilder::anOrder()->build();
        $this->orderFixture = new OrderFixture($order);

        $invoiceFixture = new InvoiceFixture(InvoiceBuilder::forOrder($order)->build());

        self::assertInstanceOf(InvoiceInterface::class, $this->invoiceRepository->get($invoiceFixture->getId()));
        self::assertFalse($order->canInvoice());
    }

    /**
     * Create an invoice for some of the order's items.
     *
     * @test
     * @throws \Exception
     */
    public function createPartialInvoices()
    {
        $order = OrderBuilder::anOrder()->withProducts(
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

        $invoiceFixture = new InvoiceFixture(
            InvoiceBuilder::forOrder($order)
                ->withItem($orderItemIds['foo'], 2)
                ->withItem($orderItemIds['bar'], 2)
                ->build()
        );

        self::assertInstanceOf(InvoiceInterface::class, $this->invoiceRepository->get($invoiceFixture->getId()));
        self::assertTrue($order->canInvoice());

        $invoiceFixture = new InvoiceFixture(
            InvoiceBuilder::forOrder($order)
                ->withItem($orderItemIds['bar'], 1)
                ->build()
        );

        self::assertInstanceOf(InvoiceInterface::class, $this->invoiceRepository->get($invoiceFixture->getId()));
        self::assertFalse($order->canInvoice());
    }
}
