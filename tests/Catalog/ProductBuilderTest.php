<?php

namespace TddWizard\Fixtures\Test\Catalog;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use TddWizard\Fixtures\Catalog\ProductBuilder;
use TddWizard\Fixtures\Catalog\ProductFixture;
use TddWizard\Fixtures\Catalog\ProductFixtureRollback;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class ProductBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ProductFixture[]
     */
    private $products = [];

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->products = [];
    }

    protected function tearDown()
    {
        if (! empty($this->products)) {
            foreach ($this->products as $product) {
                ProductFixtureRollback::create()->execute($product);
            }
        }
    }

    public function testDefaultSimpleProduct()
    {
        $productFixture = new ProductFixture(
            ProductBuilder::aSimpleProduct()->build()
        );
        $this->products[] = $productFixture;
        /** @var Product $product */
        $product = $this->productRepository->getById($productFixture->getId());
        $this->assertEquals('Simple Product', $product->getName());
        $this->assertEquals([1], $product->getWebsiteIds());
        $this->assertEquals(1, $product->getData('tax_class_id'));
        $this->assertTrue(
            $product->getExtensionAttributes()->getStockItem()->getIsInStock()
        );
        $this->assertEquals(100, $product->getExtensionAttributes()->getStockItem()->getQty());
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     */
    public function testSimpleProductWithSpecificAttributes()
    {
        /** @var StoreManagerInterface $storeManager */
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $secondWebsiteId = $storeManager->getWebsite('test')->getId();
        $productFixture = new ProductFixture(
            ProductBuilder::aSimpleProduct()
                ->withSku('foobar')
                ->withName('Foo Bar')
                ->withStatus(Status::STATUS_DISABLED)
                ->withVisibility(Product\Visibility::VISIBILITY_NOT_VISIBLE)
                ->withWebsiteIds([$secondWebsiteId])
                ->withPrice(9.99)
                ->withTaxClassId(2)
                ->withIsInStock(false)
                ->withStockQty(-1)
                ->withCustomAttributes(
                    [
                        'cost' => 2.0
                    ]
                )
                ->build()
        );
        $this->products[] = $productFixture;
        /** @var Product $product */
        $product = $this->productRepository->getById($productFixture->getId());
        $this->assertEquals('foobar', $product->getSku());
        $this->assertEquals('Foo Bar', $product->getName());
        $this->assertEquals(Status::STATUS_DISABLED, $product->getStatus());
        $this->assertEquals(Product\Visibility::VISIBILITY_NOT_VISIBLE, $product->getVisibility());
        $this->assertEquals([1, $secondWebsiteId], $product->getWebsiteIds()); // current website (1) is always added by ProductRepository
        $this->assertEquals(9.99, $product->getPrice());
        $this->assertEquals(2, $product->getData('tax_class_id'));
        $this->assertFalse(
            $product->getExtensionAttributes()->getStockItem()->getIsInStock()
        );
        $this->assertEquals(-1, $product->getExtensionAttributes()->getStockItem()->getQty());
        $this->assertEquals(2.0, $product->getCustomAttribute('cost')->getValue());
    }

    public function testRandomSkuOnBuild()
    {
        $builder = ProductBuilder::aSimpleProduct();
        $productFixture = new ProductFixture(
            $builder->build()
        );
        $this->assertRegExp('/[0-9a-f]{32}/', $productFixture->getSku());
        $this->products[] = $productFixture;

        $otherProductFixture = new ProductFixture(
            $builder->build()
        );
        $this->assertRegExp('/[0-9a-f]{32}/', $otherProductFixture->getSku());
        $this->assertNotEquals($productFixture->getSku(), $otherProductFixture->getSku());
        $this->products[] = $otherProductFixture;
    }
}
