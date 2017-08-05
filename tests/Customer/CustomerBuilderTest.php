<?php

namespace TddWizard\Fixtures\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class CustomerBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CustomerFixture[]
     */
    private $customers = [];

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $this->customers = [];
    }

    protected function tearDown()
    {
        if (! empty($this->customers)) {
            foreach ($this->customers as $customer) {
                CustomerFixtureRollback::create()->execute($customer);
            }
        }
    }

    public function testDefaultCustomer()
    {
        $customerFixture = new CustomerFixture(
            CustomerBuilder::aCustomer()->build()
        );
        $this->customers[] = $customerFixture;
        $customer = $this->customerRepository->getById($customerFixture->getId());
        $this->assertNull($customer->getConfirmation(), 'Customer should be active');
        $this->assertEquals(1, $customer->getWebsiteId(), 'Default website');
        $this->assertEquals(1, $customer->getStoreId(), 'Default store');
        $this->assertEquals(1, $customer->getGroupId(), 'Default customer group');
    }

    public function testDefaultCustomerWithDefaultAddresses()
    {
        $customerFixture = new CustomerFixture(
            CustomerBuilder::aCustomer()
                ->withAddresses(
                    AddressBuilder::anAddress()->asDefaultShipping(),
                    AddressBuilder::anAddress()->asDefaultBilling()
                )->build()
        );
        $this->customers[] = $customerFixture;
        $customer = $this->customerRepository->getById($customerFixture->getId());

        $this->assertCount(
            2,
            $customer->getAddresses(),
            'Customer should have two addresses'
        );
        $this->assertNotEquals(
            $customer->getDefaultBilling(),
            $customer->getDefaultShipping(),
            'Default shipping address should be different from default billing address'
        );
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_store.php
     */
    public function testCustomerWithSpecificAttributes()
    {
        /** @var StoreManagerInterface $storeManager */
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $secondStoreId = $storeManager->getStore('fixture_second_store')->getId();
        $customerFixture = new CustomerFixture(
            CustomerBuilder::aCustomer()
                ->withEmail('example@example.com')
                ->withGroupId(2)
                ->withStoreId($secondStoreId)
                ->withPrefix('Agent')
                ->withFirstname('James')
                ->withMiddlename('H')
                ->withLastname('Bond')
                ->withSuffix('007')
                ->withTaxvat('7')
                ->build()
        );
        $this->customers[] = $customerFixture;
        $customer = $this->customerRepository->getById($customerFixture->getId());
        $this->assertEquals('example@example.com', $customer->getEmail());
        $this->assertEquals(2, $customer->getGroupId());
        $this->assertEquals($secondStoreId, $customer->getStoreId());
        $this->assertEquals('Agent', $customer->getPrefix());
        $this->assertEquals('James', $customer->getFirstname());
        $this->assertEquals('H', $customer->getMiddlename());
        $this->assertEquals('Bond', $customer->getLastname());
        $this->assertEquals('007', $customer->getSuffix());
        $this->assertEquals('7', $customer->getTaxvat());
    }

    public function testAddressWithSpecificAttributes()
    {
        $customerFixture = new CustomerFixture(
            CustomerBuilder::aCustomer()
                ->withAddresses(
                    AddressBuilder::anAddress()
                        ->withFirstname('Wasch')
                        ->withLastname('Bär')
                        ->withStreet('Trierer Str. 791')
                        ->withTelephone('555-666-777')
                        ->withCompany('integer_net')
                        ->withCountryId('DE')
                        ->withRegionId(88)
                        ->withPostcode('52078')
                        ->withCity('Aachen')
                        ->asDefaultShipping()
                        ->asDefaultBilling()
                )->build()
        );
        $this->customers[] = $customerFixture;
        $customer = $this->customerRepository->getById($customerFixture->getId());
        $address = $customer->getAddresses()[0];
        $this->assertEquals('Wasch', $address->getFirstname());
        $this->assertEquals('Bär', $address->getLastname());
        $this->assertEquals(['Trierer Str. 791'], $address->getStreet());
        $this->assertEquals('555-666-777', $address->getTelephone());
        $this->assertEquals('integer_net', $address->getCompany());
        $this->assertEquals('DE', $address->getCountryId());
        $this->assertEquals('52078', $address->getPostcode());
        $this->assertEquals('Aachen', $address->getCity());
        $this->assertEquals(88, $address->getRegionId());
    }
}
