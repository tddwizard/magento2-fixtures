<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Customer;

use InvalidArgumentException;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Directory\Model\Region;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

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

    public function asDefaultShipping(): AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setIsDefaultShipping(true);
        return $builder;
    }

    public function asDefaultBilling(): AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setIsDefaultBilling(true);
        return $builder;
    }

    public function withPrefix(string $prefix): AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setPrefix($prefix);
        return $builder;
    }

    public function withFirstname(string $firstname): AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setFirstname($firstname);
        return $builder;
    }

    public function withMiddlename(string $middlename): AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setMiddlename($middlename);
        return $builder;
    }

    public function withLastname(string $lastname): AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setLastname($lastname);
        return $builder;
    }

    public function withSuffix(string $suffix): AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setSuffix($suffix);
        return $builder;
    }

    public function withStreet(string $street): AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setStreet((array)$street);
        return $builder;
    }

    public function withCompany(string $company): AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setCompany($company);
        return $builder;
    }

    public function withTelephone(string $telephone): AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setTelephone($telephone);
        return $builder;
    }

    public function withPostcode(string $postcode): AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setPostcode($postcode);
        return $builder;
    }

    public function withCity(string $city): AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setCity($city);
        return $builder;
    }

    public function withCountryId(string $countryId): AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setCountryId($countryId);
        return $builder;
    }

    public function withRegionId(int $regionId): AddressBuilder
    {
        $builder = clone $this;
        $builder->address->setRegionId($regionId);
        return $builder;
    }

    /**
     * @param mixed[] $values
     * @return AddressBuilder
     */
    public function withCustomAttributes(array $values): AddressBuilder
    {
        $builder = clone $this;
        foreach ($values as $code => $value) {
            $builder->address->setCustomAttribute($code, $value);
        }
        return $builder;
    }

    /**
     * @return AddressInterface
     * @throws LocalizedException
     */
    public function build(): AddressInterface
    {
        return $this->addressRepository->save($this->address);
    }

    public function buildWithoutSave(): AddressInterface
    {
        return clone $this->address;
    }

    public static function anAddress() : AddressBuilder
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
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
}
