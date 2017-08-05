<?php

namespace TddWizard\Fixtures\Catalog;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;

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

    public static function create(ObjectManagerInterface $objectManager = null)
    {
        if ($objectManager === null) {
            $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        }
        return new self(
            $objectManager->get(Registry::class),
            $objectManager->get(ProductRepositoryInterface::class)
        );
    }

    public function execute(ProductFixture $productFixture)
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        $this->productRepository->deleteById($productFixture->getSku());

        $this->registry->unregister('isSecureArea');
    }
}
