<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

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

    public static function create(ObjectManagerInterface $objectManager = null): CustomerFixtureRollback
    {
        if ($objectManager === null) {
            $objectManager = Bootstrap::getObjectManager();
        }
        return new self(
            $objectManager->get(Registry::class),
            $objectManager->get(CustomerRepositoryInterface::class)
        );
    }

    /**
     * @param CustomerFixture ...$customerFixtures
     * @throws LocalizedException
     */
    public function execute(CustomerFixture ...$customerFixtures): void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        foreach ($customerFixtures as $customerFixture) {
            $this->customerRepository->deleteById($customerFixture->getId());
        }

        $this->registry->unregister('isSecureArea');
    }
}
