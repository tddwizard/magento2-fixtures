<?php

namespace TddWizard\Fixtures\Customer;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Builder to be used by fixtures
 */
class AddressBuilder
{
    /**
     * @var AddressInterface
     */
    private $address;
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    public function __construct(AddressRepositoryInterface $addressRepository, AddressInterface $address)
    {
        $this->address = $address;
        $this->addressRepository = $addressRepository;
    }

    public function __clone()
    {
        $this->address = clone $this->address;
    }

    public function withCustomAttributes(array $values) : AddressBuilder
    {
        $builder = clone $this;
        foreach ($values as $code => $value) {
            $builder->address->setCustomAttribute($code, $value);
        }
        return $builder;
    }

    public static function anAddress(ObjectManagerInterface $objectManager = null) : AddressBuilder
    {
        if ($objectManager === null) {
            $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        }
        /** @var AddressInterface $address */
        $address = $objectManager->create(AddressInterface::class);
        $address
            ->setTelephone('3468676')
            ->setPostcode('75477')
            ->setCountryId('US')
            ->setCity('CityM')
            ->setCompany('CompanyName')
            ->setStreet(['Green str, 67'])
            ->setLastname('Smith')
            ->setFirstname('John')
            ->setRegionId(1);
        return new self($objectManager->create(AddressRepositoryInterface::class), $address);
    }

    public function asDefaultShipping() : AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setIsDefaultShipping(true);
        return $builder;
    }

    public function asDefaultBilling() : AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setIsDefaultBilling(true);
        return $builder;
    }

    public function build() : AddressInterface
    {
        return $this->addressRepository->save($this->address);
    }

    public function buildWithoutSave() : AddressInterface
    {
        return clone $this->address;
    }

    public function withFirstname($firstname) : AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setFirstname($firstname);
        return $builder;
    }

    public function withLastname($lastname) : AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setLastname($lastname);
        return $builder;
    }

    public function withStreet($street) : AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setStreet((array) $street);
        return $builder;
    }

    public function withCompany($company) : AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setCompany($company);
        return $builder;
    }

    public function withTelephone($telephone) : AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setTelephone($telephone);
        return $builder;
    }

    public function withPostcode($postcode) : AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setPostcode($postcode);
        return $builder;
    }

    public function withCity($city) : AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setCity($city);
        return $builder;
    }

    public function withCountryId($countryId) : AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setCountryId($countryId);
        return $builder;
    }

    public function withRegionId($regionId) : AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setRegionId($regionId);
        return $builder;
    }

}
