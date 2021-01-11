<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Catalog;

use TddWizard\Fixtures\Catalog\OptionBuilder;
use TddWizard\Fixtures\Catalog\OptionFixture;
use TddWizard\Fixtures\Catalog\OptionFixtureRollback;
use Magento\Eav\Model\Entity\Attribute\OptionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option as OptionResource;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class OptionFixtureRollbackTest extends TestCase
{

    /** @var OptionResource */
    private $optionResourceModel;

    /** @var OptionFactory */
    private $optionFactory;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->optionFactory = $objectManager->get(OptionFactory::class);
        $this->optionResourceModel = $objectManager->get(OptionResource::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/dropdown_attribute.php
     */
    public function testRollbackSingleOptionFixture(): void
    {
        $userDefinedAttributeCode = 'dropdown_attribute';
        $optionFixture = new OptionFixture(
            OptionBuilder::anOptionFor($userDefinedAttributeCode)->build(),
            $userDefinedAttributeCode
        );
        OptionFixtureRollback::create()->execute($optionFixture);

        /** @var \Magento\Eav\Model\Entity\Attribute\Option $option */
        $option = $this->optionFactory->create();
        $this->optionResourceModel->load($option, $optionFixture->getOption()->getId());
        self::assertNull($option->getId());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/dropdown_attribute.php
     */
    public function testRollbackMultipleOptionFixtures(): void
    {
        $userDefinedAttributeCode = 'dropdown_attribute';
        $optionFixture = new OptionFixture(
            OptionBuilder::anOptionFor($userDefinedAttributeCode)->build(),
            $userDefinedAttributeCode
        );
        $otherOptionFixture = new OptionFixture(
            OptionBuilder::anOptionFor($userDefinedAttributeCode)->build(),
            $userDefinedAttributeCode
        );
        OptionFixtureRollback::create()->execute($optionFixture, $otherOptionFixture);

        /** @var \Magento\Eav\Model\Entity\Attribute\Option $option */
        $option = $this->optionFactory->create();
        $this->optionResourceModel->load($option, $optionFixture->getOption()->getId());
        self::assertNull($option->getId());

        $this->optionResourceModel->load($option, $otherOptionFixture->getOption()->getId());
        self::assertNull($option->getId());
    }
}
