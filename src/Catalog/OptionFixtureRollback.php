<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Catalog;

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
    private $optionManagement;

    /**
     * OptionFixtureRollback constructor.
     *
     * @param Registry $registry
     * @param AttributeOptionManagementInterface $optionManagement
     */
    public function __construct(Registry $registry, AttributeOptionManagementInterface $optionManagement)
    {
        $this->registry = $registry;
        $this->optionManagement = $optionManagement;
    }

    /**
     * Create the object.
     *
     * @return OptionFixtureRollback
     */
    public static function create(): OptionFixtureRollback
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
            $this->optionManagement->delete(
                Product::ENTITY,
                $optionFixture->getAttributeCode(),
                $optionFixture->getOption()->getId()
            );
        }

        $this->registry->unregister('isSecureArea');
    }
}
