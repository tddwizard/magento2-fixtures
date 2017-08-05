<?php

namespace TddWizard\Fixtures\Test\Integration;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\TestFramework\Helper\Bootstrap;
use TddWizard\Fixtures\Catalog\ProductBuilder;
use TddWizard\Fixtures\Catalog\ProductFixture;
use TddWizard\Fixtures\Catalog\ProductFixtureRollback;

class ProductBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductFixture
     */
    private $product;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp()
    {
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
    }

    protected function tearDown()
    {
        if ($this->product) {
            ProductFixtureRollback::create()->execute($this->product);
        }
    }

    public function testDefaultSimpleProduct()
    {
        $this->product = new ProductFixture(
            ProductBuilder::aSimpleProduct()->build()
        );
        /** @var Product $product */
        $product = $this->productRepository->getById($this->product->getId());
        $this->assertEquals('Simple Product', $product->getName());
        $this->assertEquals([1], $product->getWebsiteIds());
        $this->assertEquals(1, $product->getData('tax_class_id'));
        $this->assertTrue(
            $product->getExtensionAttributes()->getStockItem()->getIsInStock()
        );
        $this->assertEquals(100, $product->getExtensionAttributes()->getStockItem()->getQty());
    }
}
