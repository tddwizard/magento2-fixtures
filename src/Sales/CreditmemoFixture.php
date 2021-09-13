<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Sales;

use Magento\Sales\Api\Data\CreditmemoInterface;

class CreditmemoFixture
{
    /**
     * @var CreditmemoInterface
     */
    private $creditmemo;

    public function __construct(CreditmemoInterface $creditmemo)
    {
        $this->creditmemo = $creditmemo;
    }

    public function getCreditmemo(): CreditmemoInterface
    {
        return $this->creditmemo;
    }

    public function getId(): int
    {
        return (int) $this->creditmemo->getEntityId();
    }
}
