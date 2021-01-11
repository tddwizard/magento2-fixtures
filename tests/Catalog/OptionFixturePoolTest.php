<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Catalog;

use TddWizard\Fixtures\Catalog\OptionBuilder;
use TddWizard\Fixtures\Catalog\OptionFixturePool;
use Magento\Eav\Model\Entity\Attribute\Option as AttributeOption;
use Magento\Eav\Model\Entity\Attribute\OptionFactory as AttributeOptionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option as OptionResource;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class OptionFixturePoolTest extends TestCase
{

    /**
     * @var OptionFixturePool
     */
    private $optionFixtures;

    /**
     * @var OptionResource
     */
    private $optionResourceModel;

    /**
     * @var string
     */
    private $dbAttributeCode = 'dropdown_attribute';

    /**
     * @var AttributeOptionFactory
     */
    private $optionFactory;

    protected function setUp(): void
    {
        $this->optionFixtures = new OptionFixturePool();
        $this->optionFactory = Bootstrap::getObjectManager()->get(AttributeOptionFactory::class);
        $this->optionResourceModel = Bootstrap::getObjectManager()->get(OptionResource::class);
    }

    public function testLastOptionFixtureReturnedByDefault(): void
    {
        $firstOption = $this->createOption();
        $lastOption = $this->createOption();
        $this->optionFixtures->add($firstOption, 'option_1');
        $this->optionFixtures->add($lastOption, 'option_2');
        $optionFixture = $this->optionFixtures->get();
        self::assertEquals($lastOption->getId(), $optionFixture->getOption()->getId());
    }

    public function testExceptionThrownWhenAccessingEmptyOptionPool(): void
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->optionFixtures->get();
    }

    public function testOptionFixtureReturnedByKey(): void
    {
        $firstOption = $this->createOption();
        $lastOption = $this->createOption();
        $this->optionFixtures->add($firstOption, 'option_1', 'first');
        $this->optionFixtures->add($lastOption, 'option_2', 'last');
        $optionFixture = $this->optionFixtures->get('first');
        self::assertEquals($firstOption->getId(), $optionFixture->getOption()->getId());
    }

    public function testExceptionThrownWhenAccessingNonexistingKey(): void
    {
        $option = $this->createOption();
        $this->optionFixtures->add($option, 'option_1', 'foo');
        $this->expectException(\OutOfBoundsException::class);
        $this->optionFixtures->get('bar');
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/dropdown_attribute.php
     */
    public function testRollbackRemovesOptionsFromPool(): void
    {
        $option = $this->createOptionInDb($this->dbAttributeCode);
        $this->optionFixtures->add($option, $this->dbAttributeCode);
        $this->optionFixtures->rollback();
        $this->expectException(\OutOfBoundsException::class);
        $this->optionFixtures->get();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/dropdown_attribute.php
     */
    public function testRollbackDeletesOptionsFromDb(): void
    {
        $option = $this->createOptionInDb($this->dbAttributeCode);
        $this->optionFixtures->add($option, $this->dbAttributeCode);
        $this->optionFixtures->rollback();
        $option = $this->optionFactory->create();
        $this->optionResourceModel->load($option, $option->getId());
        self::assertEmpty($option->getId());
    }

    /**
     * Creates a dummy option object
     *
     * @return AttributeOption
     */
    private function createOption(): AttributeOption
    {
        static $nextId = 1;
        /** @var AttributeOption $option */
        $option = Bootstrap::getObjectManager()->create(AttributeOption::class);
        $option->setId($nextId++);

        return $option;
    }

    /**
     * Uses builder to create a customer
     *
     * @param string $attributeCode
     * @return AttributeOption
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    private function createOptionInDb(string $attributeCode): AttributeOption
    {
        return OptionBuilder::anOptionFor($attributeCode)->withLabel('Testing')->build();
    }
}
