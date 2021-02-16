<?php

namespace TddWizard\Fixtures\Catalog;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class BundleProductBuilderTest extends TestCase
{

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ProductFixture[]
     */
    private $products = [];

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductLinkManagementInterface
     */
    private $productLinkManagement;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->productLinkManagement = $this->objectManager->create(ProductLinkManagementInterface::class);
        $this->products = [];
    }

    protected function tearDown(): void
    {
        if (!empty($this->products)) {
            foreach ($this->products as $product) {
                ProductFixtureRollback::create()->execute($product);
            }
        }
    }

    public function testDefaultBundleProduct()
    {
        $childProduct1 = new ProductFixture(
            ProductBuilder::aSimpleProduct()
            ->withPrice(100)
            ->build()
        );
        $this->products[] = $childProduct1;
        $childProduct2 = new ProductFixture(
            ProductBuilder::aSimpleProduct()
            ->withPrice(200)
            ->build()
        );
        $this->products[] = $childProduct2;

        $bundleProductFixture = new ProductFixture(
            BundleProductBuilder::aBundleProduct()
                ->withBundleOptionsData(
                    // bundle options data
                    [
                        [
                            'title' => 'Checkbox Options',
                            'default_title' => 'Checkbox Options',
                            'type' => 'checkbox',
                            'required' => 1,
                            'delete' => '',
                        ]
                    ],
                    // bundle selections data
                    [
                        [
                            [
                                'sku' => $childProduct1->getSku(),
                                'selection_qty' => 1,
                                'selection_price_value' => 0,
                                'selection_can_change_qty' => 1,
                                'delete' => '',
                            ],
                            [
                                'sku' => $childProduct2->getSku(),
                                'selection_qty' => 1,
                                'selection_price_value' => 0,
                                'selection_can_change_qty' => 1,
                                'delete' => '',
                            ]

                        ]
                    ]
                )->build()
        );
        $this->products[] = $bundleProductFixture;

        /** @var Product $product */
        $bundleProduct = $this->productRepository->getById($bundleProductFixture->getId());
        $this->assertEquals(Type::TYPE_BUNDLE, $bundleProduct->getTypeId());
        $this->assertEquals('Bundle Product', $bundleProduct->getName());
        $this->assertEquals([1], $bundleProduct->getWebsiteIds());
        $this->assertEquals(1, $bundleProduct->getData('tax_class_id'));
        $this->assertTrue(
            $bundleProduct->getExtensionAttributes()->getStockItem()->getIsInStock()
        );
        $this->assertEquals(0, $bundleProduct->getExtensionAttributes()->getStockItem()->getQty());

        /** @var $bundleType \Magento\Bundle\Model\Product\Type */
        $bundleType = $bundleProduct->getTypeInstance();
        $options = $bundleType->getOptionsCollection($bundleProduct);
        $this->assertCount(1, $options->getItems());
        $option = $options->getFirstItem();
        $this->assertEquals('Checkbox Options', $option->getData('title'));
        $this->assertEquals('checkbox', $option->getData('type'));
        $this->assertEquals('1', $option->getData('required'));

        $childProducts = $this->productLinkManagement->getChildren($bundleProduct->getSku());
        $this->assertCount(2, $childProducts);
    }
}
