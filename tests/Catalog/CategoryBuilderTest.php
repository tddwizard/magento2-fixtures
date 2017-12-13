<?php

namespace TddWizard\Fixtures\Catalog;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class CategoryBuilderTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CategoryFixture[]
     */
    private $categories = [];

    /**
     * @var ProductFixture[]
     */
    private $products = [];

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->categoryRepository = $this->objectManager->create(CategoryRepositoryInterface::class);
        $this->categories = [];
        $this->products = [];
    }

    protected function tearDown()
    {
        if (!empty($this->categories)) {
            foreach ($this->categories as $product) {
                CategoryFixtureRollback::create()->execute($product);
            }
        }
        if (! empty($this->products)) {
            foreach ($this->products as $product) {
                ProductFixtureRollback::create()->execute($product);
            }
        }
    }

    public function testDefaultTopLevelCategory()
    {
        $categoryFixture = new CategoryFixture(
            CategoryBuilder::topLevelCategory()->build()
        );
        $this->categories[] = $categoryFixture;

        /** @var Category $category */
        $category = $this->categoryRepository->get($categoryFixture->getId());
        $this->assertEquals('Top Level Category', $category->getName(), 'Category name');
        $this->assertEquals([0, 1], $category->getStoreIds(), 'Assigned store ids');
        $this->assertEquals('1/2/' . $categoryFixture->getId(), $category->getPath(), 'Category path');
    }

    public function testCategoryWithProducts()
    {
        $product1 = new ProductFixture(ProductBuilder::aSimpleProduct()->build());
        $product2 = new ProductFixture(ProductBuilder::aSimpleProduct()->build());
        $categoryFixture = new CategoryFixture(
            CategoryBuilder::topLevelCategory()->withProducts([$product1->getSku(), $product2->getSku()])->build()
        );
        $this->products[] = $product1;
        $this->products[] = $product2;
        $this->categories[] = $categoryFixture;

        /** @var Category $category */
        $category = $this->categoryRepository->get($categoryFixture->getId());

        $this->assertEquals(
            [$product1->getId() => 0, $product2->getId() => 1],
            $category->getProductsPosition(),
            'Product positions'
        );
    }
}