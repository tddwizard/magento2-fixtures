<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Catalog;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Api\Data\ProductWebsiteLinkInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\ProductWebsiteLinkRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Indexer\Model\IndexerFactory;
use Magento\TestFramework\Helper\Bootstrap;

class GroupedProductBuilder extends ProductBuilder
{

    /**
     * @var ProductInterface[]
     */
    private $childProducts = [];

    /**
     * @var array
     */
    private $childProductsOptions = [];

    public static function aGroupedProduct(): self
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Product $product */
        $product = $objectManager->create(ProductInterface::class);

        $product
            ->setTypeId(Grouped::TYPE_CODE)
            ->setAttributeSetId(4)
            ->setName('Grouped Product')
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED)
            ->setData('tax_class_id', 1);

        return new self(
            $objectManager->create(ProductRepositoryInterface::class),
            $objectManager->create(ProductLinkInterfaceFactory::class),
            $objectManager->create(StockItemRepositoryInterface::class),
            $objectManager->create(ProductWebsiteLinkRepositoryInterface::class),
            $objectManager->create(ProductWebsiteLinkInterfaceFactory::class),
            $objectManager->create(IndexerFactory::class),
            $product,
            [1],
            []
        );
    }

    /**
     * @param ProductInterface[] $childProducts
     * @param array $options
     * @return GroupedProductBuilder
     */
    public function withAssociatedProducts(array $childProducts, array $options = []): self
    {
        $builder = clone $this;
        $builder->childProducts = $childProducts;
        $builder->childProductsOptions = $childProducts;
        return $builder;
    }

    /**
     * @param ProductInterface $product
     */
    protected function prepareProductForSave(ProductInterface $product): void
    {
        parent::prepareProductForSave($product);

        if (!$this->childProducts) {
            return;
        }

        if (!$product->getSku()) {
            $product->setSku(sha1(uniqid('', true)));
        }
        $product->setCustomAttribute('url_key', $product->getSku());
        $product->setData('category_ids', $this->categoryIds);

        $productLinks = [];
        foreach ($this->childProducts as $key => $childProduct) {
            $productLink = $this->productLinkFactory->create();
            $productLink->setSku($product->getSku())
                ->setLinkType('associated')
                ->setLinkedProductSku($childProduct->getSku())
                ->setLinkedProductType($childProduct->getTypeId())
                ->setPosition($this->childProductsOptions[$key]['position'] ?? 0)
                ->getExtensionAttributes()
                ->setQty($this->childProductsOptions[$key]['qty'] ?? 1);

            $productLinks[] = $productLink;
        }

        // set relations
        $product->setProductLinks($productLinks);
        $product->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);
    }
}
