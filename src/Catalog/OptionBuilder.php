<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Catalog;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Api\Data\AttributeOptionLabelInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Create a source-model option for an attribute.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OptionBuilder
{

    /**
     * @var AttributeOptionManagementInterface
     */
    private $attributeOptionManagement;

    /**
     * @var AttributeOptionInterface
     */
    private $option;

    /**
     * @var AttributeOptionLabelInterface
     */
    private $optionLabel;

    /**
     * @var string
     */
    private $attributeCode;

    /**
     * OptionBuilder constructor.
     *
     * @param AttributeOptionManagementInterface $attributeOptionManagement
     * @param AttributeOptionInterface $option
     * @param AttributeOptionLabelInterface $optionLabel
     * @param string $attributeCode
     */
    public function __construct(
        AttributeOptionManagementInterface $attributeOptionManagement,
        AttributeOptionInterface $option,
        AttributeOptionLabelInterface $optionLabel,
        string $attributeCode
    ) {
        $this->attributeOptionManagement = $attributeOptionManagement;
        $this->option = $option;
        $this->optionLabel = $optionLabel;
        $this->attributeCode = $attributeCode;
    }

    /**
     * Clone the builder.
     */
    public function __clone()
    {
        $this->option = clone $this->option;
    }

    /**
     * Create an option.
     *
     * @param string $attributeCode
     * @return OptionBuilder
     */
    public static function anOption(string $attributeCode): OptionBuilder
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var AttributeOptionLabelInterface $optionLabel */
        $optionLabel = $objectManager->create(AttributeOptionLabelInterface::class);
        $label = uniqid('Name ', true);
        $optionLabel->setStoreId(0); // TODO: handle better
        $optionLabel->setLabel($label);

        /** @var AttributeOptionInterface $option */
        $option = $objectManager->create(AttributeOptionInterface::class);
        $option->setLabel($label);
        $option->setStoreLabels([$optionLabel]);
        $option->setSortOrder(0);
        $option->setIsDefault(false);

        return new static(
            $objectManager->create(AttributeOptionManagementInterface::class),
            $option,
            $optionLabel,
            $attributeCode
        );
    }

    /**
     * Set label.
     *
     * @param string $label
     * @return OptionBuilder
     */
    public function withLabel(string $label): OptionBuilder
    {
        $builder = clone $this;
        $builder->optionLabel->setLabel($label);
        $builder->option->setStoreLabels([$builder->optionLabel]);
        $builder->option->setLabel($label);

        return $builder;
    }

    /**
     * Set sort order.
     *
     * @param int $sortOrder
     * @return OptionBuilder
     */
    public function withSortOrder(int $sortOrder): OptionBuilder
    {
        $builder = clone $this;
        $builder->option->setSortOrder($sortOrder);

        return $builder;
    }

    /**
     * Set default.
     *
     * @param bool $isDefault
     * @return OptionBuilder
     */
    public function withIsDefault(bool $isDefault): OptionBuilder
    {
        $builder = clone $this;
        $builder->option->setIsDefault($isDefault);

        return $builder;
    }

    /**
     * Set store ID.
     *
     * @param int $storeId
     * @return OptionBuilder
     */
    public function withStoreId(int $storeId): OptionBuilder
    {
        $builder = clone $this;
        $builder->optionLabel->setStoreId($storeId);

        return $builder;
    }

    /**
     * Build the option and apply it to the attribute.
     *
     * @return int
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function build(): int
    {
        $builder = clone $this;

        // add the option
        $this->attributeOptionManagement->add(
            \Magento\Catalog\Model\Product::ENTITY,
            $builder->attributeCode,
            $builder->option
        );

        return $this->getOptionId();
    }

    /**
     * Get the option ID.
     *
     * @return int
     */
    private function getOptionId(): int
    {
        $objectManager = Bootstrap::getObjectManager();
        // the add option above does not return the option, so we need to retrieve it
        $attributeRepository = $objectManager->get(ProductAttributeRepositoryInterface::class);
        $attribute = $attributeRepository->get($this->attributeCode);
        $attributeValues[$attribute->getAttributeId()] = [];

        // We have to generate a new sourceModel instance each time through to prevent it from
        // referencing its _options cache. No other way to get it to pick up newly-added values.
        $tableFactory = $objectManager->get(\Magento\Eav\Model\Entity\Attribute\Source\TableFactory::class);
        $sourceModel = $tableFactory->create();
        $sourceModel->setAttribute($attribute);
        foreach ($sourceModel->getAllOptions() as $option) {
            $attributeValues[$attribute->getAttributeId()][$option['label']] = $option['value'];
        }
        if (isset($attributeValues[$attribute->getAttributeId()][$this->optionLabel->getLabel()])) {
            return (int)$attributeValues[$attribute->getAttributeId()][$this->optionLabel->getLabel()];
        }

        throw new \RuntimeException('Error building option');
    }
}
