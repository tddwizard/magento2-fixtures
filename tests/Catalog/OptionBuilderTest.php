<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Catalog;

use Magento\Eav\Model\Entity\Attribute\OptionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option as OptionResource;
use TddWizard\Fixtures\Catalog\OptionFixtureRollback;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class OptionBuilderTest extends TestCase
{

    private $options = [];

    /** @var AttributeOptionManagementInterface */
    private $attributeOptionManagement;

    /** @var OptionResource */
    private $optionResourceModel;

    /** @var OptionFactory */
    private $optionFactory;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->attributeOptionManagement = $this->objectManager->get(AttributeOptionManagementInterface::class);
        $this->optionFactory = $this->objectManager->get(OptionFactory::class);
        $this->optionResourceModel = $this->objectManager->get(OptionResource::class);
    }

    protected function tearDown(): void
    {
        if (! empty($this->options)) {
            foreach ($this->options as $optionFixture) {
                OptionFixtureRollback::create()->execute($optionFixture);
            }
        }
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/dropdown_attribute.php
     */
    public function testAddOption()
    {
        /*
         * Values from core fixture files
         */
        $userDefinedAttributeCode = 'dropdown_attribute';
        $optionFixture = new OptionFixture(
            OptionBuilder::anOption($userDefinedAttributeCode)->build(), $userDefinedAttributeCode
        );
        $this->options[] = $optionFixture;

        /** @var \Magento\Eav\Model\Entity\Attribute\Option $option */
        $option = $this->optionFactory->create();
        $this->optionResourceModel->load($option, $optionFixture->getOptionId());

        self::assertEquals($optionFixture->getOption->getId()(), $option->getId());
//        $savedItem = null;
//        foreach ($items as $item)
//        {
//            if ($item->getLabel() === $optionFixture->getOptionLabel()) {
//                $savedItem = $item;
//                break;
//            }
//        }
//        self::assertNotNull($savedItem);

    }
}
