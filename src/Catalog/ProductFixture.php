<?php

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

    public function getId() : int
    {
        return $this->product->getId();
    }

    public function getSku() : string
    {
        return $this->product->getSku();
    }

}
