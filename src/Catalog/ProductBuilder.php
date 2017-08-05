<?php

namespace TddWizard\Fixtures\Catalog;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductWebsiteLinkInterface;
use Magento\Catalog\Api\Data\ProductWebsiteLinkInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\ProductWebsiteLinkRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;

class ProductBuilder
{
    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var int[]
     */
    private $websiteIds = [];
    /**
     * @var ProductWebsiteLinkRepositoryInterface
     */
    private $productWebsiteLinkRepository;
    /**
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;
    /**
     * @var ProductWebsiteLinkInterfaceFactory
     */
    private $productWebsiteLinkInterfaceFactory;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        StockItemRepositoryInterface $stockItemRepository,
        ProductWebsiteLinkRepositoryInterface $productWebsiteLinkRepository,
        ProductWebsiteLinkInterfaceFactory $productWebsiteLinkInterfaceFactory,
        ProductInterface $product,
        array $websiteIds
    ) {
        $this->productRepository = $productRepository;
        $this->productWebsiteLinkRepository = $productWebsiteLinkRepository;
        $this->stockItemRepository = $stockItemRepository;
        $this->productWebsiteLinkInterfaceFactory = $productWebsiteLinkInterfaceFactory;
        $this->product = $product;
        $this->websiteIds = $websiteIds;
    }

    public function __clone()
    {
        $this->product = clone $this->product;
    }

    public static function aSimpleProduct(ObjectManagerInterface $objectManager = null) : ProductBuilder
    {
        if ($objectManager === null) {
            $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        }
        /** @var ProductInterface $product */
        $product = $objectManager->create(ProductInterface::class);

        $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
            ->setAttributeSetId(4)
            ->setName('Simple Product')
            ->setSku('simple-' . sha1(uniqid('', true)))
            ->setPrice(10)
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED);
        $product->addData([
            'tax_class_id' => 1,
            'description' => 'Description',
            'url_key' => $product->getSku()
        ]);
        /** @var StockItemInterface $stockItem */
        $stockItem = $objectManager->create(StockItemInterface::class);
        $stockItem->setManageStock(true)
            ->setQty(100)
            ->setIsQtyDecimal(false)
            ->setIsInStock(true);
        $product->setExtensionAttributes(
            $product->getExtensionAttributes()->setStockItem($stockItem)
        );

        return new self(
            $objectManager->create(ProductRepositoryInterface::class),
            $objectManager->create(StockItemRepositoryInterface::class),
            $objectManager->create(ProductWebsiteLinkRepositoryInterface::class),
            $objectManager->create(ProductWebsiteLinkInterfaceFactory::class),
            $product,
            [1]
        );
    }

    public function withPrice($price) : ProductBuilder
    {
        $builder = clone $this;
        $builder->product->setPrice($price);
        return $builder;
    }


    public function withCustomAttributes(array $values) : ProductBuilder
    {
        $builder = clone $this;
        foreach ($values as $code => $value) {
            $builder->product->setCustomAttribute($code, $value);
        }
        return $builder;
    }

    public function build() : ProductInterface
    {
        $product = $this->productRepository->save($this->product);
        foreach ($this->websiteIds as $websiteId) {
            /** @var ProductWebsiteLinkInterface $websiteLink */
            $websiteLink = $this->productWebsiteLinkInterfaceFactory->create();
            $websiteLink->setWebsiteId($websiteId)->setSku($product->getSku());
            $this->productWebsiteLinkRepository->save($websiteLink);
        }
        return $product;
    }

}
