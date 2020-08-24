<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Catalog;

use Magento\Catalog\Api\Data\ProductInterface;

class ProductFixture
{
    /**
     * @var ProductInterface
     */
    private $product;

    public function __construct(ProductInterface $product)
    {
        $this->product = $product;
    }

    public function getId(): int
    {
        return (int) $this->product->getId();
    }

    public function getSku(): string
    {
        return $this->product->getSku();
    }
}
