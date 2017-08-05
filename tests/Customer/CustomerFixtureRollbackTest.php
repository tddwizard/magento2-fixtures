<?php

namespace TddWizard\Fixtures\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class CustomerFixtureRollbackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    protected function setUp()
    {
        $this->customerRepository = Bootstrap::getObjectManager()->create(CustomerRepositoryInterface::class);
    }

    public function testRollbackSingleCustomerFixture()
    {
        $customerFixture = new CustomerFixture(
            CustomerBuilder::aCustomer()->build()
        );
        CustomerFixtureRollback::create()->execute($customerFixture);
        $this->setExpectedException(NoSuchEntityException::class);
        $this->customerRepository->getById($customerFixture->getId());
    }

    public function testRollbackMultipleCustomerFixtures()
    {
        $customerFixture = new CustomerFixture(
            CustomerBuilder::aCustomer()->build()
        );
        $otherCustomerFixture = new CustomerFixture(
            CustomerBuilder::aCustomer()->build()
        );
        CustomerFixtureRollback::create()->execute($customerFixture, $otherCustomerFixture);
        $customerDeleted = false;
        try {
            $this->customerRepository->getById($customerFixture->getId());
        } catch (NoSuchEntityException $e) {
            $customerDeleted = true;
        }
        $this->assertTrue($customerDeleted, 'First customer should be deleted');
        $this->setExpectedException(NoSuchEntityException::class);
        $this->customerRepository->getById($otherCustomerFixture->getId());
    }
}
