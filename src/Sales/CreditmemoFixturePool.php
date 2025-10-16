<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Sales;

use Magento\Sales\Api\Data\CreditmemoInterface;

class CreditmemoFixturePool
{

    /**
     * @var CreditmemoFixture[]
     */
    private $creditmemoFixtures = [];

    public function add(CreditmemoInterface $creditmemo, ?string $key = null): void
    {
        if ($key === null) {
            $this->creditmemoFixtures[] = new CreditmemoFixture($creditmemo);
        } else {
            $this->creditmemoFixtures[$key] = new CreditmemoFixture($creditmemo);
        }
    }

    /**
     * Returns creditmemo fixture by key, or last added if key not specified
     *
     * @param string|null $key
     * @return CreditmemoFixture
     */
    public function get(?string $key = null): CreditmemoFixture
    {
        if ($key === null) {
            $key = \array_key_last($this->creditmemoFixtures);
        }
        if ($key === null || !array_key_exists($key, $this->creditmemoFixtures)) {
            throw new \OutOfBoundsException('No matching creditmemo found in fixture pool');
        }
        return $this->creditmemoFixtures[$key];
    }
}
