<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Checkout;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Helper\Bootstrap;

class CartBuilder
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var DataObject[][] Array in the form [sku => [buyRequest]] (multiple requests per sku are possible)
     */
    private $addToCartRequests;

    /**
     * @var bool
     */
    private $useDefaultCustomerAddresses = false;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        AddressRepositoryInterface $addressRepository,
        Cart $cart
    ) {
        $this->productRepository = $productRepository;
        $this->addressRepository = $addressRepository;
        $this->cart = $cart;
        $this->addToCartRequests = [];
    }

    public static function forCurrentSession(): CartBuilder
    {
        $objectManager = Bootstrap::getObjectManager();
        return new static(
            $objectManager->create(ProductRepositoryInterface::class),
            $objectManager->create(AddressRepositoryInterface::class),
            $objectManager->create(Cart::class)
        );
    }

    public function withSimpleProduct(string $sku, float $qty = 1): CartBuilder
    {
        $result = clone $this;
        $result->addToCartRequests[$sku][] = new DataObject(['qty' => $qty]);
        return $result;
    }

    public function withReservedOrderId(string $orderId): CartBuilder
    {
        $result = clone $this;
        $result->cart->getQuote()->setReservedOrderId($orderId);
        return $result;
    }

    /**
     * Lower-level API to support arbitrary products
     *
     * @param string $sku
     * @param int $qty
     * @param array[] $request
     * @return CartBuilder
     */
    public function withProductRequest($sku, $qty = 1, $request = []): CartBuilder
    {
        $result = clone $this;
        $requestInfo = array_merge(['qty' => $qty], $request);
        $result->addToCartRequests[$sku][] = new DataObject($requestInfo);
        return $result;
    }

    /**
     * Added.
     *
     * @return CartBuilder
     */
    public function withDefaultCustomerAddress(): CartBuilder
    {
        $result = clone $this;
        $result->useDefaultCustomerAddresses = true;

        return $result;
    }

    /**
     * @return Cart
     * @throws LocalizedException
     */
    public function build(): Cart
    {
        foreach ($this->addToCartRequests as $sku => $requests) {
            /** @var Product $product */
            $product = $this->productRepository->get($sku);
            foreach ($requests as $requestInfo) {
                $this->cart->addProduct($product, $requestInfo);
            }
        }

        // added
        if ($this->useDefaultCustomerAddresses && $this->cart->getQuote()->getCustomerId()) {
            $billingAddress = $this->cart->getQuote()->getBillingAddress();
            $billingAddress->importCustomerAddressData(
                $this->addressRepository->getById(
                    (int)$this->cart->getQuote()->getCustomer()->getDefaultBilling()
                )
            );

            if (!$this->cart->getQuote()->isVirtual()) {
                $shippingAddress = $this->cart->getQuote()->getShippingAddress();
                $shippingAddress->importCustomerAddressData(
                    $this->addressRepository->getById(
                        (int)$this->cart->getQuote()->getCustomer()->getDefaultShipping()
                    )
                );
            }
        }

        $this->cart->save();
        return $this->cart;
    }
}
