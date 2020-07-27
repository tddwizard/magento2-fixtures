<?php

namespace TddWizard\Fixtures\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Builder to be used by fixtures
 */
class CustomerBuilder
{
    /**
     * @var CustomerInterface
     */
    private $customer;

    /**
     * @var string
     */
    private $password;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AddressBuilder[]
     */
    private $addressBuilders;

    /**
     * @var Encryptor
     */
    private $encryptor;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerInterface $customer,
        Encryptor $encryptor,
        string $password,
        AddressBuilder ...$addressBuilders
    ) {
        $this->customerRepository = $customerRepository;
        $this->customer = $customer;
        $this->encryptor = $encryptor;
        $this->password = $password;
        $this->addressBuilders = $addressBuilders;
    }

    public function __clone()
    {
        $this->customer = clone $this->customer;
    }

    public static function aCustomer(ObjectManagerInterface $objectManager = null): CustomerBuilder
    {
        if ($objectManager === null) {
            $objectManager = Bootstrap::getObjectManager();
        }
        /** @var CustomerInterface $customer */
        $customer = $objectManager->create(CustomerInterface::class);
        $customer->setWebsiteId(1)
            ->setGroupId(1)
            ->setStoreId(1)
            ->setPrefix('Mr.')
            ->setFirstname('John')
            ->setMiddlename('A')
            ->setLastname('Smith')
            ->setSuffix('Esq.')
            ->setTaxvat('12')
            ->setGender(0);
        $password = 'Test#123';
        return new self(
            $objectManager->create(CustomerRepositoryInterface::class),
            $customer,
            $objectManager->create(Encryptor::class),
            $password
        );
    }

    public function withAddresses(AddressBuilder ...$addressBuilders): CustomerBuilder
    {
        $builder = clone $this;
        $builder->addressBuilders = $addressBuilders;
        return $builder;
    }

    public function withEmail(string $email): CustomerBuilder
    {
        $builder = clone $this;
        $builder->customer->setEmail($email);
        return $builder;
    }

    public function withGroupId($groupId): CustomerBuilder
    {
        $builder = clone $this;
        $builder->customer->setGroupId($groupId);
        return $builder;
    }

    public function withStoreId($storeId): CustomerBuilder
    {
        $builder = clone $this;
        $builder->customer->setStoreId($storeId);
        return $builder;
    }

    public function withWebsiteId($websiteId): CustomerBuilder
    {
        $builder = clone $this;
        $builder->customer->setWebsiteId($websiteId);
        return $builder;
    }

    public function withPrefix($prefix): CustomerBuilder
    {
        $builder = clone $this;
        $builder->customer->setPrefix($prefix);
        return $builder;
    }

    public function withFirstname($firstname): CustomerBuilder
    {
        $builder = clone $this;
        $builder->customer->setFirstname($firstname);
        return $builder;
    }

    public function withMiddlename($middlename): CustomerBuilder
    {
        $builder = clone $this;
        $builder->customer->setMiddlename($middlename);
        return $builder;
    }

    public function withLastname($lastname): CustomerBuilder
    {
        $builder = clone $this;
        $builder->customer->setLastname($lastname);
        return $builder;
    }

    public function withSuffix($suffix): CustomerBuilder
    {
        $builder = clone $this;
        $builder->customer->setSuffix($suffix);
        return $builder;
    }

    public function withTaxvat($taxvat): CustomerBuilder
    {
        $builder = clone $this;
        $builder->customer->setTaxvat($taxvat);
        return $builder;
    }

    public function withDob($dob): CustomerBuilder
    {
        $builder = clone $this;
        $builder->customer->setDob($dob);
        return $builder;
    }

    public function withCustomAttributes(array $values): CustomerBuilder
    {
        $builder = clone $this;
        foreach ($values as $code => $value) {
            $builder->customer->setCustomAttribute($code, $value);
        }
        return $builder;
    }

    public function withConfirmation(string $confirmation): CustomerBuilder
    {
        $builder = clone $this;
        $builder->customer->setConfirmation($confirmation);
        return $builder;
    }

    /**
     * @return CustomerInterface
     * @throws LocalizedException
     */
    public function build(): CustomerInterface
    {
        $builder = clone $this;
        if (!$builder->customer->getEmail()) {
            $builder->customer->setEmail(sha1(uniqid('', true)) . '@example.com');
        }
        $addresses = array_map(
            function (AddressBuilder $addressBuilder) {
                return $addressBuilder->buildWithoutSave();
            },
            $builder->addressBuilders
        );
        $builder->customer->setAddresses($addresses);
        $customer = $builder->saveNewCustomer();
        /*
         * Magento automatically sets random confirmation key for new account with password.
         * We need to save again with our own confirmation (null for confirmed customer)
         */
        $customer->setConfirmation($builder->customer->getConfirmation());
        return $builder->customerRepository->save($customer);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod) False positive: the method is used in build() on the cloned builder
     *
     * @return CustomerInterface
     * @throws LocalizedException
     */
    private function saveNewCustomer(): CustomerInterface
    {
        return $this->customerRepository->save($this->customer, $this->encryptor->getHash($this->password, true));
    }
}
