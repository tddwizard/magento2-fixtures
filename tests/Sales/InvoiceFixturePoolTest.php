<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Sales;

use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class InvoiceFixturePoolTest extends TestCase
{
    /**
     * @var InvoiceFixturePool
     */
    private $invoiceFixtures;
    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    protected function setUp(): void
    {
        $this->invoiceFixtures = new InvoiceFixturePool();
        $this->invoiceRepository = Bootstrap::getObjectManager()->create(InvoiceRepositoryInterface::class);
    }

    public function testLastInvoiceFixtureReturnedByDefault()
    {
        $firstInvoice = InvoiceBuilder::forOrder(OrderBuilder::anOrder()->build())->build();
        $lastInvoice = InvoiceBuilder::forOrder(OrderBuilder::anOrder()->build())->build();
        $this->invoiceFixtures->add($firstInvoice);
        $this->invoiceFixtures->add($lastInvoice);
        $invoiceFixture = $this->invoiceFixtures->get();
        $this->assertEquals($lastInvoice->getEntityId(), $invoiceFixture->getId());
    }

    public function testExceptionThrownWhenAccessingEmptyInvoicePool()
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->invoiceFixtures->get();
    }

    public function testInvoiceFixtureReturnedByKey()
    {
        $firstInvoice = InvoiceBuilder::forOrder(OrderBuilder::anOrder()->build())->build();
        $lastInvoice = InvoiceBuilder::forOrder(OrderBuilder::anOrder()->build())->build();
        $this->invoiceFixtures->add($firstInvoice, 'first');
        $this->invoiceFixtures->add($lastInvoice, 'last');
        $invoiceFixture = $this->invoiceFixtures->get('first');
        $this->assertEquals($firstInvoice->getEntityId(), $invoiceFixture->getId());
    }

    public function testExceptionThrownWhenAccessingNonexistingKey()
    {
        $invoice = InvoiceBuilder::forOrder(OrderBuilder::anOrder()->build())->build();
        $this->invoiceFixtures->add($invoice, 'foo');
        $this->expectException(\OutOfBoundsException::class);
        $this->invoiceFixtures->get('bar');
    }

}
