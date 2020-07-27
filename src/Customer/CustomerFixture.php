<?php

namespace TddWizard\Fixtures\Customer;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Object that can be returned from customer fixture, contains ids for test expectations
 */
class CustomerFixture
{
    /**
     * @var CustomerInterface
     */
    private $customer;

    public function __construct(CustomerInterface $customer)
    {
        $this->customer = $customer;
    }

    public function getDefaultShippingAddressId(): int
    {
        return $this->customer->getDefaultShipping();
    }

    public function getDefaultBillingAddressId(): int
    {
        return $this->customer->getDefaultBilling();
    }

    public function getOtherAddressId(): int
    {
        return $this->getNonDefaultAddressIds()[0];
    }

    public function getNonDefaultAddressIds(): array
    {
        return array_values(
            array_diff(
                $this->getAllAddressIds(),
                [$this->getDefaultBillingAddressId(), $this->getDefaultShippingAddressId()]
            )
        );
    }

    public function getAllAddressIds(): array
    {
        return array_map(
            function (AddressInterface $address) {
                return $address->getId();
            },
            $this->customer->getAddresses()
        );
    }

    public function getId(): int
    {
        return $this->customer->getId();
    }

    public function getConfirmation(): string
    {
        return $this->customer->getConfirmation();
    }

    public function getEmail(): string
    {
        return $this->customer->getEmail();
    }

    public function login(Session $session = null): void
    {
        if ($session === null) {
            $objectManager = Bootstrap::getObjectManager();
            $objectManager->removeSharedInstance(Session::class);
            $session = $objectManager->get(Session::class);
        }
        $session->setCustomerId($this->getId());
    }

    public function logout(Session $session = null): void
    {
        if ($session === null) {
            $objectManager = Bootstrap::getObjectManager();
            $session = $objectManager->get(Session::class);
        }

        $session->logout();
    }
}
