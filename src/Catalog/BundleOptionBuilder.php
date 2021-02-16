<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Catalog;

/**
 * Create options for bundle products.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BundleOptionBuilder
{

    // \Magento\Bundle\Api\Data\OptionInterface creates the option; for example, "Hard Drive".
    // \Magento\Bundle\Api\Data\BundleOptionInterface creates the option selections (?); for example: "2TB", "3TB"
    // \Magento\TestFramework\Bundle\Model\PrepareBundleLinks handles setting the options onto the parent product (?)
}
