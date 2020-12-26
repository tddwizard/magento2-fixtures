<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Catalog;

/**
 * Class OptionFixture
 */
class OptionFixture
{

    /**
     * @var int
     */
    private $optionId;

    /**
     * @var string
     */
    private $attributeCode;

    /**
     * OptionFixture constructor.
     *
     * @param int $optionId
     * @param string $attributeCode
     */
    public function __construct(int $optionId, string $attributeCode)
    {
        $this->optionId = $optionId;
        $this->attributeCode = $attributeCode;
    }

    /**
     * Get the option ID.
     *
     * @return int
     */
    public function getOptionId(): int
    {
        return $this->optionId;
    }

    /**
     * Get the attribute code.
     *
     * @return string
     */
    public function getAttributeCode(): string
    {
        return $this->attributeCode;
    }

    /**
     * Rollback the option(s).
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function rollback(): void
    {
        OptionFixtureRollback::create()->execute($this);
    }
}
