<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Sales;

use Magento\Sales\Api\Data\InvoiceInterface;

class InvoiceFixturePool
{

    /**
     * @var InvoiceFixture[]
     */
    private $invoiceFixtures = [];

    public function add(InvoiceInterface $invoice, ?string $key = null): void
    {
        if ($key === null) {
            $this->invoiceFixtures[] = new InvoiceFixture($invoice);
        } else {
            $this->invoiceFixtures[$key] = new InvoiceFixture($invoice);
        }
    }

    /**
     * Returns invoice fixture by key, or last added if key not specified
     *
     * @param string|null $key
     * @return InvoiceFixture
     */
    public function get(?string $key = null): InvoiceFixture
    {
        if ($key === null) {
            $key = \array_key_last($this->invoiceFixtures);
        }
        if ($key === null || !array_key_exists($key, $this->invoiceFixtures)) {
            throw new \OutOfBoundsException('No matching invoice found in fixture pool');
        }
        return $this->invoiceFixtures[$key];
    }
}
