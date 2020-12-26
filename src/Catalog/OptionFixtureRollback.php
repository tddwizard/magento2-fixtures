<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Catalog;

namespace Coffee\FixtureBuilders\Catalog;

use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Roll back one or more options.
 *
 * @internal Use OptionFixture::rollback() instead.
 */
class OptionFixtureRollback
{

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var AttributeOptionManagementInterface
     */
    private $attributeOptionManagement;

    /**
     * OptionFixtureRollback constructor.
     *
     * @param Registry $registry
     * @param AttributeOptionManagementInterface $attributeOptionManagement
     */
    public function __construct(Registry $registry, AttributeOptionManagementInterface $attributeOptionManagement)
    {
        $this->registry = $registry;
        $this->attributeOptionManagement = $attributeOptionManagement;
    }

    /**
     * Create the object.
     *
     * @return OptionFixtureRollback
     */
    public static function create():
    OptionFixtureRollback //phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction
    {
        $objectManager = Bootstrap::getObjectManager();
        return new self(
            $objectManager->get(Registry::class),
            $objectManager->get(AttributeOptionManagementInterface::class)
        );
    }

    /**
     * Remove the given option(s).
     *
     * @param OptionFixture ...$optionFixtures
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function execute(OptionFixture ...$optionFixtures): void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        foreach ($optionFixtures as $optionFixture) {
            $this->attributeOptionManagement->delete(
                Product::ENTITY,
                $optionFixture->getAttributeCode(),
                $optionFixture->getOptionId()
            );
        }

        $this->registry->unregister('isSecureArea');
    }
}
