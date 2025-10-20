<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Catalog;

use Magento\Eav\Model\Entity\Attribute\Option as AttributeOption;
use function array_values as values;

/**
 * Class OptionFixturePool
 */
class OptionFixturePool
{

    /**
     * @var OptionFixture[]
     */
    private $optionFixtures = [];

    public function add(AttributeOption $option, string $attributecode, ?string $key = null): void
    {
        if ($key === null) {
            $this->optionFixtures[] = new OptionFixture($option, $attributecode);
        } else {
            $this->optionFixtures[$key] = new OptionFixture($option, $attributecode);
        }
    }

    /**
     * Returns option fixture by key, or last added if key not specified
     *
     * @param string|null $key
     * @return OptionFixture
     */
    public function get(?string $key = null): OptionFixture
    {
        if ($key === null) {
            $key = \array_key_last($this->optionFixtures);
        }
        if ($key === null || !\array_key_exists($key, $this->optionFixtures)) {
            throw new \OutOfBoundsException('No matching option found in fixture pool');
        }
        return $this->optionFixtures[$key];
    }

    public function rollback(): void
    {
        OptionFixtureRollback::create()->execute(...values($this->optionFixtures));
        $this->optionFixtures = [];
    }
}
