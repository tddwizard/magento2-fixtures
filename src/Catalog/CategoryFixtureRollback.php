<?php

namespace TddWizard\Fixtures\Catalog;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;

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

    public static function create(ObjectManagerInterface $objectManager = null)
    {
        if ($objectManager === null) {
            $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        }
        return new self(
            $objectManager->get(Registry::class),
            $objectManager->get(CategoryRepositoryInterface::class)
        );
    }

    public function execute(CategoryFixture ...$categoryFixtures)
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        foreach ($categoryFixtures as $categoryFixture) {
            $this->categoryRepository->deleteByIdentifier($categoryFixture->getId());
        }

        $this->registry->unregister('isSecureArea');
    }
}
