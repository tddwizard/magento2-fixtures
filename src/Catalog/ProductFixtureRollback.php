<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Catalog;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @internal Use ProductFixture::rollback() or ProductFixturePool::rollback() instead
 */
class ProductFixtureRollback
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(Registry $registry, ProductRepositoryInterface $productRepository)
    {
        $this->registry = $registry;
        $this->productRepository = $productRepository;
    }

    public static function create(ObjectManagerInterface $objectManager = null): ProductFixtureRollback
    {
        if ($objectManager === null) {
            $objectManager = Bootstrap::getObjectManager();
        }
        return new self(
            $objectManager->get(Registry::class),
            $objectManager->get(ProductRepositoryInterface::class)
        );
    }

    /**
     * @param ProductFixture ...$productFixtures
     * @throws LocalizedException
     */
    public function execute(ProductFixture ...$productFixtures): void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        foreach ($productFixtures as $productFixture) {
            $this->productRepository->deleteById($productFixture->getSku());
        }

        $this->registry->unregister('isSecureArea');
    }
}
