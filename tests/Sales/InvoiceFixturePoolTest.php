<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Sales;

use Magento\Sales\Api\Data\InvoiceInterface;
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
        $firstInvoice = $this->createInvoice();
        $lastInvoice = $this->createInvoice();
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
        $firstInvoice = $this->createInvoice();
        $lastInvoice = $this->createInvoice();
        $this->invoiceFixtures->add($firstInvoice, 'first');
        $this->invoiceFixtures->add($lastInvoice, 'last');
        $invoiceFixture = $this->invoiceFixtures->get('first');
        $this->assertEquals($firstInvoice->getEntityId(), $invoiceFixture->getId());
    }

    public function testExceptionThrownWhenAccessingNonexistingKey()
    {
        $invoice = $this->createInvoice();
        $this->invoiceFixtures->add($invoice, 'foo');
        $this->expectException(\OutOfBoundsException::class);
        $this->invoiceFixtures->get('bar');
    }

    /**
     * @return InvoiceInterface
     * @throws \Exception
     */
    private function createInvoice(): InvoiceInterface
    {
        static $nextId = 1;
        /** @var InvoiceInterface $invoice */
        $invoice = Bootstrap::getObjectManager()->create(InvoiceInterface::class);
        $invoice->setEntityId($nextId++);
        return $invoice;
    }
}
