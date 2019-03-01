<?php

namespace TddWizard\Fixtures\Catalog;

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Indexer\Model\IndexerFactory;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Can run the fulltext catalog index once from tests to ensure that tables for all stores are created
 */
class FulltextIndex
{
    /**
     * @var bool
     */
    private static $created = false;

    /**
     * @var IndexerFactory
     */
    private $indexerFactory;

    public function __construct(IndexerFactory $indexerFactory)
    {
        $this->indexerFactory = $indexerFactory;
    }

    public static function ensureTablesAreCreated()
    {
        if (!self::$created) {
            (new self(Bootstrap::getObjectManager()->create(IndexerFactory::class)))->reindex();
        }
    }

    public function reindex()
    {
        /** @var \Magento\Indexer\Model\Indexer $indexer */
        $indexer = $this->indexerFactory->create();
        $indexer->load('catalogsearch_fulltext');
        $indexer->reindexAll();
    }
}