<?php

namespace TddWizard\Fixtures\Catalog;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductWebsiteLinkInterface;
use Magento\Catalog\Api\Data\ProductWebsiteLinkInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\ProductWebsiteLinkRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductRepository;
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
            ->setPrice(10)
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED);
        $product->addData([
            'tax_class_id' => 1,
            'description' => 'Description',
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

    public function withSku(string $sku) : ProductBuilder
    {
        $builder = clone $this;
        $builder->product->setSku($sku);
        return $builder;
    }

    public function withName(string $name) : ProductBuilder
    {
        $builder = clone $this;
        $builder->product->setName($name);
        return $builder;
    }

    public function withStatus(int $status) : ProductBuilder
    {
        $builder = clone $this;
        $builder->product->setStatus($status);
        return $builder;
    }

    public function withVisibility(int $visibility) : ProductBuilder
    {
        $builder = clone $this;
        $builder->product->setVisibility($visibility);
        return $builder;
    }

    public function withWebsiteIds(array $websiteIds) : ProductBuilder
    {
        $builder = clone $this;
        $builder->websiteIds = $websiteIds;
        return $builder;
    }

    public function withPrice(float $price) : ProductBuilder
    {
        $builder = clone $this;
        $builder->product->setPrice($price);
        return $builder;
    }

    public function withTaxClassId($taxClassId) : ProductBuilder
    {
        $builder = clone $this;
        $builder->product->setData('tax_class_id', $taxClassId);
        return $builder;
    }

    public function withIsInStock(bool $inStock) : ProductBuilder
    {
        $builder = clone $this;
        $builder->product->getExtensionAttributes()->getStockItem()->setIsInStock($inStock);
        return $builder;
    }

    public function withStockQty($qty) : ProductBuilder
    {
        $builder = clone $this;
        $builder->product->getExtensionAttributes()->getStockItem()->setQty($qty);
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
        $builder = clone $this;
        if (!$builder->product->getSku()) {
            $builder->product->setSku(sha1(uniqid('', true)));
        }
        $builder->product->addData([
            'url_key' => $builder->product->getSku()
        ]);
        $product = $builder->productRepository->save($builder->product);
        foreach ($builder->websiteIds as $websiteId) {
            /** @var ProductWebsiteLinkInterface $websiteLink */
            $websiteLink = $builder->productWebsiteLinkInterfaceFactory->create();
            $websiteLink->setWebsiteId($websiteId)->setSku($product->getSku());
            $builder->productWebsiteLinkRepository->save($websiteLink);
        }
        return $product;
    }

}
