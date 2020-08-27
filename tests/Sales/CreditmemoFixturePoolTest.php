<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Sales;

use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class CreditmemoFixturePoolTest extends TestCase
{
    /**
     * @var CreditmemoFixturePool
     */
    private $creditmemoFixtures;
    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    protected function setUp(): void
    {
        $this->creditmemoFixtures = new CreditmemoFixturePool();
        $this->creditmemoRepository = Bootstrap::getObjectManager()->create(CreditmemoRepositoryInterface::class);
    }

    public function testLastCreditmemoFixtureReturnedByDefault()
    {
        $firstCreditmemo = $this->createCreditmemo();
        $lastCreditmemo = $this->createCreditmemo();
        $this->creditmemoFixtures->add($firstCreditmemo);
        $this->creditmemoFixtures->add($lastCreditmemo);
        $creditmemoFixture = $this->creditmemoFixtures->get();
        $this->assertEquals($lastCreditmemo->getEntityId(), $creditmemoFixture->getId());
    }

    public function testExceptionThrownWhenAccessingEmptyCreditmemoPool()
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->creditmemoFixtures->get();
    }

    public function testCreditmemoFixtureReturnedByKey()
    {
        $firstCreditmemo = $this->createCreditmemo();
        $lastCreditmemo = $this->createCreditmemo();
        $this->creditmemoFixtures->add($firstCreditmemo, 'first');
        $this->creditmemoFixtures->add($lastCreditmemo, 'last');
        $creditmemoFixture = $this->creditmemoFixtures->get('first');
        $this->assertEquals($firstCreditmemo->getEntityId(), $creditmemoFixture->getId());
    }

    public function testExceptionThrownWhenAccessingNonexistingKey()
    {
        $creditmemo = $this->createCreditmemo();
        $this->creditmemoFixtures->add($creditmemo, 'foo');
        $this->expectException(\OutOfBoundsException::class);
        $this->creditmemoFixtures->get('bar');
    }

    /**
     * @return CreditmemoInterface
     * @throws \Exception
     */
    private function createCreditmemo(): CreditmemoInterface
    {
        static $nextId = 1;
        /** @var CreditmemoInterface $creditmemo */
        $creditmemo = Bootstrap::getObjectManager()->create(CreditmemoInterface::class);
        $creditmemo->setEntityId($nextId++);
        return $creditmemo;
    }
}
