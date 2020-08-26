<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Catalog;

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
        $firstProduct = ProductBuilder::aSimpleProduct()->build();
        $lastProduct = ProductBuilder::aSimpleProduct()->build();
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
        $firstProduct = ProductBuilder::aSimpleProduct()->build();
        $lastProduct = ProductBuilder::aSimpleProduct()->build();
        $this->productFixtures->add($firstProduct, 'first');
        $this->productFixtures->add($lastProduct, 'last');
        $productFixture = $this->productFixtures->get('first');
        $this->assertEquals($firstProduct->getSku(), $productFixture->getSku());
    }

    public function testProductFixtureReturnedByNumericKey()
    {
        $firstProduct = ProductBuilder::aSimpleProduct()->build();
        $lastProduct = ProductBuilder::aSimpleProduct()->build();
        $this->productFixtures->add($firstProduct);
        $this->productFixtures->add($lastProduct);
        $productFixture = $this->productFixtures->get(0);
        $this->assertEquals($firstProduct->getSku(), $productFixture->getSku());
    }

    public function testExceptionThrownWhenAccessingNonexistingKey()
    {
        $product = ProductBuilder::aSimpleProduct()->build();
        $this->productFixtures->add($product, 'foo');
        $this->expectException(\OutOfBoundsException::class);
        $this->productFixtures->get('bar');
    }

    public function testRollbackRemovesProductsFromPool()
    {
        $product = ProductBuilder::aSimpleProduct()->build();
        $this->productFixtures->add($product);
        $this->productFixtures->rollback();
        $this->expectException(\OutOfBoundsException::class);
        $this->productFixtures->get();
    }
    public function testRollbackDeletesProductsFromDb()
    {
        $product = ProductBuilder::aSimpleProduct()->build();
        $this->productFixtures->add($product);
        $this->productFixtures->rollback();
        $this->expectException(NoSuchEntityException::class);
        $this->productRepository->get($product->getSku());
    }
}
