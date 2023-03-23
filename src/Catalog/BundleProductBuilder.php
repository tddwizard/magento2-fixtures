<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Catalog;

use Magento\Bundle\Api\Data\OptionInterface as BundleOptionInterface;
use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Model\Product\Type as BundleProductType;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Api\Data\ProductWebsiteLinkInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\ProductWebsiteLinkRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\Indexer\Model\IndexerFactory;
use Magento\TestFramework\Bundle\Model\PrepareBundleLinks;
use Magento\TestFramework\Helper\Bootstrap;

class BundleProductBuilder extends ProductBuilder
{
    /**
     * @var array
     */
    private $bundleOptionsData = [];

    /**
     * @var array
     */
    private $bundleSelectionsData = [];

    /**
     * @return BundleProductBuilder
     */
    public static function aBundleProduct(): BundleProductBuilder
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Product $product */
        $product = $objectManager->create(ProductInterface::class);
        $product
            ->setTypeId(BundleProductType::TYPE_CODE)
            ->setAttributeSetId(4)
            ->setName('Bundle Product')
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED)
            ->addData(
                [
                    'price_type' => Price::PRICE_TYPE_DYNAMIC,
                    'sku_type' => 1, // dynamic
                    'weight_type' => 1, // dynamic
                    'price_view' => 0, // range
                    'shipment_type' => 1, // ship together
                    'tax_class_id' => 1
                ]
            );

        // @note: price index values are missing when stock is not set on bundle
        /** @var StockItemInterface $stockItem */
        $stockItem = $objectManager->create(StockItemInterface::class);
        $stockItem->setManageStock(true)
            ->setQty(0)
            ->setIsInStock(true);
        $product->setExtensionAttributes(
            $product->getExtensionAttributes()->setStockItem($stockItem)
        );

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
     * @return self
     */
    public function withDynamicPrice(): self
    {
        $builder = clone $this;
        $builder->product->setData('price_type', Price::PRICE_TYPE_DYNAMIC);
        return $builder;
    }

    /**
     * @param float $price
     * @return self
     */
    public function withFixedPrice(float $price): self
    {
        $builder = clone $this;
        $builder->product->setData('price_type', Price::PRICE_TYPE_FIXED);
        $builder->product->setPrice($price);
        return $builder;
    }

    /**
     * @return self
     */
    public function withDynamicSku(): self
    {
        $builder = clone $this;
        $builder->product->setData('sku_type', 0);
        return $builder;
    }

    /**
     * @param string $sku
     * @return self
     */
    public function withFixedSku(string $sku): self
    {
        $builder = clone $this;
        $builder->product->setData('sku_type', 1);
        $builder->product->setSku($sku);
        return $builder;
    }

    /**
     * @return self
     */
    public function withDynamicWeight(): self
    {
        $builder = clone $this;
        $builder->product->setData('weight_type', 1);

        return $builder;
    }

    /**
     * @param float $weight
     * @return self
     */
    public function withFixedWeight(float $weight): self
    {
        $builder = clone $this;
        $builder->product->setData('weight_type', 0);
        $builder->product->setWeight($weight);

        return $builder;
    }

    /**
     * @return self
     */
    public function withShipItemsTogether(): self
    {
        $builder = clone $this;
        $builder->product->setData('shipment_type', 1);

        return $builder;
    }

    /**
     * @return self
     */
    public function withShipItemsSeparately(): self
    {
        $builder = clone $this;
        $builder->product->setData('shipment_type', 0);

        return $builder;
    }

    /**
     * @return self
     */
    public function withPriceViewAsRange(): self
    {
        $builder = clone $this;
        $builder->product->setData('price_view', 0);

        return $builder;
    }

    /**
     * @return self
     */
    public function withPriceViewAsLowAs(): self
    {
        $builder = clone $this;
        $builder->product->setData('price_view', 1);

        return $builder;
    }

    /**
     * Set bundle options with Test Framework helper
     * See magento/dev/tests/integration/testsuite/Magento/Bundle/_files/product.php for example
     *
     * @param array $bundleOptionsData
     * @param array $bundleSelectionsData
     * @return self
     */
    public function withBundleOptionsData(
        array $bundleOptionsData,
        array $bundleSelectionsData = []
    ): self {
        $builder = clone $this;

        /** @var PrepareBundleLinks $prepareBundleLinks */
        $prepareBundleLinks = Bootstrap::getObjectManager()->get(PrepareBundleLinks::class);
        $prepareBundleLinks->execute($builder->product, $bundleOptionsData, $bundleSelectionsData);

        return $builder;
    }

    /**
     * @param BundleOptionInterface[] $bundleProductOptions
     * @return self
     */
    public function withBundleOptions(array $bundleProductOptions): self
    {
        $builder = clone $this;
        /** @var \Magento\Catalog\Api\Data\ProductExtensionInterface $extensionAttributes */
        $extensionAttributes = $builder->product->getExtensionAttributes();
        $extensionAttributes->setBundleProductOptions($bundleProductOptions);
        $builder->product->setExtensionAttributes($extensionAttributes);
        return $builder;
    }

    /**
     * @param ProductInterface $product
     */
    protected function prepareProductForSave(ProductInterface $product): void
    {
        parent::prepareProductForSave($product);

        if (!$this->bundleOptionsData) {
            return;
        }

        /** @var PrepareBundleLinks $prepareBundleLinks */
        $prepareBundleLinks = Bootstrap::getObjectManager()->get(PrepareBundleLinks::class);
        $prepareBundleLinks->execute($this->product, $this->bundleOptionsData, $this->bundleSelectionsData);
    }

}
