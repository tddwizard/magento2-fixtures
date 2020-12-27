<?php

namespace TddWizard\Fixtures\Catalog;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
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
     * @deprecated the test should be removed in next release, but we should still keep the exception in case this
     *             happens again
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     */
    public function testHelpfulErrorMessageForFulltextIndexSchedule()
    {
        $this->markTestSkipped('Since Magento 2.3.6 / 2.4.1 these transaction exceptions do not occur anymore');
        $this->expectException(\Exception::class);

        try {
            /** @var StoreManagerInterface $storeManager */
            $storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
            $secondWebsiteId = $storeManager->getWebsite('test')->getId();
            ProductBuilder::aSimpleProduct()->withWebsiteIds([$secondWebsiteId])->build();
        } catch (\Exception $exception) {
            // manual check, there is no common assertion in PHPUnit 6 / PHPUnit 9
            $this->assertNotFalse(
                preg_match(
                    '{@magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php}',
                    $exception->getMessage()
                )
            );

            throw $exception;
        }
    }

    public static function disableReindexSchedule()
    {
        /* @var IndexerInterface $model */
        $model = Bootstrap::getObjectManager()->get(IndexerRegistry::class)->get('catalogsearch_fulltext');
        self::$indexIsScheduledOrig = $model->isScheduled();
        $model->setScheduled(false);
    }

    public static function disableReindexScheduleRollback()
    {
        /* @var IndexerInterface $model */
        $model = Bootstrap::getObjectManager()->get(IndexerRegistry::class)->get('catalogsearch_fulltext');
        $model->setScheduled(self::$indexIsScheduledOrig);
    }
}
