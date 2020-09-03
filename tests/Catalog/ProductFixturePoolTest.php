<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Catalog;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class ProductFixturePoolTest extends TestCase
{
    /**
     * @var ProductFixturePool
     */
    private $productFixtures;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp(): void
    {
        $this->productFixtures = new ProductFixturePool();
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
    }

    public function testLastProductFixtureReturnedByDefault()
    {
        $firstProduct = $this->createProduct();
        $lastProduct = $this->createProduct();
        $this->productFixtures->add($firstProduct);
        $this->productFixtures->add($lastProduct);
        $productFixture = $this->productFixtures->get();
        $this->assertEquals($lastProduct->getSku(), $productFixture->getSku());
    }

    public function testExceptionThrownWhenAccessingEmptyProductPool()
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->productFixtures->get();
    }

    public function testProductFixtureReturnedByKey()
    {
        $firstProduct = $this->createProduct();
        $lastProduct = $this->createProduct();
        $this->productFixtures->add($firstProduct, 'first');
        $this->productFixtures->add($lastProduct, 'last');
        $productFixture = $this->productFixtures->get('first');
        $this->assertEquals($firstProduct->getSku(), $productFixture->getSku());
    }

    public function testProductFixtureReturnedByNumericKey()
    {
        $firstProduct = $this->createProduct();
        $lastProduct = $this->createProduct();
        $this->productFixtures->add($firstProduct);
        $this->productFixtures->add($lastProduct);
        $productFixture = $this->productFixtures->get(0);
        $this->assertEquals($firstProduct->getSku(), $productFixture->getSku());
    }

    public function testExceptionThrownWhenAccessingNonexistingKey()
    {
        $product = $this->createProduct();
        $this->productFixtures->add($product, 'foo');
        $this->expectException(\OutOfBoundsException::class);
        $this->productFixtures->get('bar');
    }

    public function testRollbackRemovesProductsFromPool()
    {
        $product = $this->createProductInDb();
        $this->productFixtures->add($product);
        $this->productFixtures->rollback();
        $this->expectException(\OutOfBoundsException::class);
        $this->productFixtures->get();
    }

    public function testRollbackWorksWithKeys()
    {
        $product = $this->createProductInDb();
        $this->productFixtures->add($product, 'key');
        $this->productFixtures->rollback();
        $this->expectException(\OutOfBoundsException::class);
        $this->productFixtures->get();
    }

    public function testRollbackDeletesProductsFromDb()
    {
        $product = $this->createProductInDb();
        $this->productFixtures->add($product);
        $this->productFixtures->rollback();
        $this->expectException(NoSuchEntityException::class);
        $this->productRepository->get($product->getSku());
    }

    /**
     * Creates a dummy product object
     *
     * @return ProductInterface
     * @throws \Exception
     */
    private function createProduct(): ProductInterface
    {
        static $nextId = 1;
        /** @var ProductInterface $product */
        $product = Bootstrap::getObjectManager()->create(ProductInterface::class);
        $product->setSku('product-' . $nextId);
        $product->setId($nextId++);
        return $product;
    }

    /**
     * Uses builder to create a product
     *
     * @return ProductInterface
     * @throws \Exception
     */
    private function createProductInDb(): ProductInterface
    {
        return ProductBuilder::aSimpleProduct()->build();
    }
}
