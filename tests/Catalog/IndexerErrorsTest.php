<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Catalog;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDataFixtureBeforeTransaction disableReindexSchedule
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class IndexerErrorsTest extends TestCase
{
    private static $indexIsScheduledOrig;

    /**
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     */
    public function testHelpfulErrorMessageForFulltextIndexSchedule()
    {
        $this->onlyRunFromMagento('2.3.0');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp(
            '{@magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php}'
        );
        /** @var StoreManagerInterface $storeManager */
        $storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $secondWebsiteId = $storeManager->getWebsite('test')->getId();
        ProductBuilder::aSimpleProduct()
            ->withWebsiteIds([$secondWebsiteId])
            ->build();
    }

    public static function disableReindexSchedule()
    {
        /* @var \Magento\Framework\Indexer\IndexerInterface $model */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Indexer\IndexerRegistry::class
        )->get('catalogsearch_fulltext');
        self::$indexIsScheduledOrig = $model->isScheduled();
        $model->setScheduled(false);

    }
    public static function disableReindexScheduleRollback()
    {
        /* @var \Magento\Framework\Indexer\IndexerInterface $model */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Indexer\IndexerRegistry::class
        )->get('catalogsearch_fulltext');
        $model->setScheduled(self::$indexIsScheduledOrig);
    }

    private function onlyRunFromMagento($magentoVersion): void
    {
        /** @var ProductMetadataInterface $productMetadata */
        $productMetadata = Bootstrap::getObjectManager()->get(ProductMetadataInterface::class);
        if (version_compare($productMetadata->getVersion(), $magentoVersion, '<')) {
            $this->markTestSkipped('Only relevant for Magento >= ' . $magentoVersion . '');
        }
    }
}
