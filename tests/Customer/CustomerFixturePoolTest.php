<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
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
        $firstCustomer = CustomerBuilder::aCustomer()->build();
        $lastCustomer = CustomerBuilder::aCustomer()->build();
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
        $firstCustomer = CustomerBuilder::aCustomer()->build();
        $lastCustomer = CustomerBuilder::aCustomer()->build();
        $this->customerFixtures->add($firstCustomer, 'first');
        $this->customerFixtures->add($lastCustomer, 'last');
        $customerFixture = $this->customerFixtures->get('first');
        $this->assertEquals($firstCustomer->getId(), $customerFixture->getId());
    }

    public function testExceptionThrownWhenAccessingNonexistingKey()
    {
        $customer = CustomerBuilder::aCustomer()->build();
        $this->customerFixtures->add($customer, 'foo');
        $this->expectException(\OutOfBoundsException::class);
        $this->customerFixtures->get('bar');
    }

    public function testRollbackRemovesCustomersFromPool()
    {
        $customer = CustomerBuilder::aCustomer()->build();
        $this->customerFixtures->add($customer);
        $this->customerFixtures->rollback();
        $this->expectException(\OutOfBoundsException::class);
        $this->customerFixtures->get();
    }
    public function testRollbackDeletesCustomersFromDb()
    {
        $customer = CustomerBuilder::aCustomer()->build();
        $this->customerFixtures->add($customer);
        $this->customerFixtures->rollback();
        $this->expectException(NoSuchEntityException::class);
        $this->customerRepository->get($customer->getId());
    }
}
