<?php

namespace TddWizard\Fixtures\Sales;

use Magento\Sales\Api\Data\InvoiceInterface;

class InvoiceFixture
{
    /**
     * @var InvoiceInterface
     */
    private $invoice;

    public function __construct(InvoiceInterface $shipment)
    {
        $this->invoice = $shipment;
    }

    public function getId(): int
    {
        return (int) $this->invoice->getEntityId();
    }
}
