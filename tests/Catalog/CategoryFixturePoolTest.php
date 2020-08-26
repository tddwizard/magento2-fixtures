<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Catalog;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class CategoryFixturePoolTest extends TestCase
{
    /**
     * @var CategoryFixturePool
     */
    private $categoryFixtures;
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    protected function setUp(): void
    {
        $this->categoryFixtures = new CategoryFixturePool();
        $this->categoryRepository = Bootstrap::getObjectManager()->create(CategoryRepositoryInterface::class);
    }

    public function testLastCategoryFixtureReturnedByDefault()
    {
        $firstCategory = CategoryBuilder::topLevelCategory()->build();
        $lastCategory = CategoryBuilder::topLevelCategory()->build();
        $this->categoryFixtures->add($firstCategory);
        $this->categoryFixtures->add($lastCategory);
        $categoryFixture = $this->categoryFixtures->get();
        $this->assertEquals($lastCategory->getId(), $categoryFixture->getId());
    }

    public function testExceptionThrownWhenAccessingEmptyCategoryPool()
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->categoryFixtures->get();
    }

    public function testCategoryFixtureReturnedByKey()
    {
        $firstCategory = CategoryBuilder::topLevelCategory()->build();
        $lastCategory = CategoryBuilder::topLevelCategory()->build();
        $this->categoryFixtures->add($firstCategory, 'first');
        $this->categoryFixtures->add($lastCategory, 'last');
        $categoryFixture = $this->categoryFixtures->get('first');
        $this->assertEquals($firstCategory->getId(), $categoryFixture->getId());
    }

    public function testCategoryFixtureReturnedByNumericKey()
    {
        $firstCategory = CategoryBuilder::topLevelCategory()->build();
        $lastCategory = CategoryBuilder::topLevelCategory()->build();
        $this->categoryFixtures->add($firstCategory);
        $this->categoryFixtures->add($lastCategory);
        $categoryFixture = $this->categoryFixtures->get(0);
        $this->assertEquals($firstCategory->getId(), $categoryFixture->getId());
    }

    public function testExceptionThrownWhenAccessingNonexistingKey()
    {
        $category = CategoryBuilder::topLevelCategory()->build();
        $this->categoryFixtures->add($category, 'foo');
        $this->expectException(\OutOfBoundsException::class);
        $this->categoryFixtures->get('bar');
    }

    public function testRollbackRemovesCategorysFromPool()
    {
        $category = CategoryBuilder::topLevelCategory()->build();
        $this->categoryFixtures->add($category);
        $this->categoryFixtures->rollback();
        $this->expectException(\OutOfBoundsException::class);
        $this->categoryFixtures->get();
    }
    public function testRollbackDeletesCategorysFromDb()
    {
        $category = CategoryBuilder::topLevelCategory()->build();
        $this->categoryFixtures->add($category);
        $this->categoryFixtures->rollback();
        $this->expectException(NoSuchEntityException::class);
        $this->categoryRepository->get($category->getId());
    }
}
