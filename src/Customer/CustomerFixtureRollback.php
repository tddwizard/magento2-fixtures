<?php

namespace TddWizard\Fixtures\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;

class CustomerFixtureRollback
{
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    public function __construct(Registry $registry, CustomerRepositoryInterface $customerRepository)
    {
        $this->registry = $registry;
        $this->customerRepository = $customerRepository;
    }

    public static function create(ObjectManagerInterface $objectManager = null)
    {
        if ($objectManager === null) {
            $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        }
        return new self(
            $objectManager->get(Registry::class),
            $objectManager->get(CustomerRepositoryInterface::class)
        );
    }

    public function execute(CustomerFixture $customerFixture)
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        $this->customerRepository->deleteById($customerFixture->getId());

        $this->registry->unregister('isSecureArea');
    }
}
