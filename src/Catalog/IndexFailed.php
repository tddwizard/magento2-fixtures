<?php

declare(strict_types=1);

namespace TddWizard\Fixtures\Catalog;

class IndexFailed extends \RuntimeException
{
    public static function becauseInitiallyTriggeredInTransaction(\Exception $previous): self
    {
        return new self(
            <<<TXT
The fixture could not be set up because creating index tables does not work within a transaction
You can either run the test without wrapping it in a transaction with:

/**
 * @magentoDbIsolation disabled
 */
 
Or set the fulltext indexer to "scheduled" before the transaction with:

/**
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php
 */ 

TXT
            ,
            0,
            $previous
        );
    }
}
