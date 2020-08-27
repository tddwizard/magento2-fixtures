<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class CustomerFixturePoolTest extends TestCase
{
    /**
     * @var CustomerFixturePool
     */
    private $customerFixtures;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    protected function setUp(): void
    {
        $this->customerFixtures = new CustomerFixturePool();
        $this->customerRepository = Bootstrap::getObjectManager()->create(CustomerRepositoryInterface::class);
    }

    public function testLastCustomerFixtureReturnedByDefault()
    {
        $firstCustomer = $this->createCustomer();
        $lastCustomer = $this->createCustomer();
        $this->customerFixtures->add($firstCustomer);
        $this->customerFixtures->add($lastCustomer);
        $customerFixture = $this->customerFixtures->get();
        $this->assertEquals($lastCustomer->getId(), $customerFixture->getId());
    }

    public function testExceptionThrownWhenAccessingEmptyCustomerPool()
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->customerFixtures->get();
    }

    public function testCustomerFixtureReturnedByKey()
    {
        $firstCustomer = $this->createCustomer();
        $lastCustomer = $this->createCustomer();
        $this->customerFixtures->add($firstCustomer, 'first');
        $this->customerFixtures->add($lastCustomer, 'last');
        $customerFixture = $this->customerFixtures->get('first');
        $this->assertEquals($firstCustomer->getId(), $customerFixture->getId());
    }

    public function testExceptionThrownWhenAccessingNonexistingKey()
    {
        $customer = $this->createCustomer();
        $this->customerFixtures->add($customer, 'foo');
        $this->expectException(\OutOfBoundsException::class);
        $this->customerFixtures->get('bar');
    }

    public function testRollbackRemovesCustomersFromPool()
    {
        $customer = $this->createCustomerInDb();
        $this->customerFixtures->add($customer);
        $this->customerFixtures->rollback();
        $this->expectException(\OutOfBoundsException::class);
        $this->customerFixtures->get();
    }
    public function testRollbackDeletesCustomersFromDb()
    {
        $customer = $this->createCustomerInDb();
        $this->customerFixtures->add($customer);
        $this->customerFixtures->rollback();
        $this->expectException(NoSuchEntityException::class);
        $this->customerRepository->get($customer->getId());
    }

    /**
     * Creates a dummy customer object
     *
     * @return CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function createCustomer(): CustomerInterface
    {
        static $nextId = 1;
        /** @var CustomerInterface $customer */
        $customer = Bootstrap::getObjectManager()->create(CustomerInterface::class);
        $customer->setId($nextId++);
        return $customer;
    }

    /**
     * Uses builder to create a customer
     *
     * @return CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function createCustomerInDb(): CustomerInterface
    {
        return CustomerBuilder::aCustomer()->build();
    }
}
