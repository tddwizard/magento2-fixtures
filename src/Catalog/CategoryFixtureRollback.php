<?php

namespace TddWizard\Fixtures\Catalog;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @internal Use CategoryFixture::rollback() or CategoryFixturePool::rollback() instead
 */
class CategoryFixtureRollback
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    public function __construct(Registry $registry, CategoryRepositoryInterface $categoryRepository)
    {
        $this->registry = $registry;
        $this->categoryRepository = $categoryRepository;
    }

    public static function create(ObjectManagerInterface $objectManager = null): CategoryFixtureRollback
    {
        if ($objectManager === null) {
            $objectManager = Bootstrap::getObjectManager();
        }
        return new self(
            $objectManager->get(Registry::class),
            $objectManager->get(CategoryRepositoryInterface::class)
        );
    }

    /**
     * @param CategoryFixture ...$categoryFixtures
     * @throws LocalizedException
     */
    public function execute(CategoryFixture ...$categoryFixtures): void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        foreach ($categoryFixtures as $categoryFixture) {
            $this->categoryRepository->deleteByIdentifier($categoryFixture->getId());
        }

        $this->registry->unregister('isSecureArea');
    }
}
