<?php

namespace TddWizard\Fixtures\Checkout;

use Magento\Checkout\Model\Cart;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Payment\Model\Config as PaymentConfig;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\TestFramework\Helper\Bootstrap;

class CustomerCheckout
{
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;
    /**
     * @var QuoteManagement
     */
    private $quoteManagement;
    /**
     * @var PaymentConfig
     */
    private $paymentConfig;
    /**
     * @var Cart
     */
    private $cart;
    /**
     * @var int|null
     */
    private $customerShippingAddressId;
    /**
     * @var int|null
     */
    private $customerBillingAddressId;
    /**
     * @var string|null
     */
    private $shippingMethodCode;
    /**
     * @var string|null
     */
    private $paymentMethodCode;

    public function __construct(
        AddressRepositoryInterface $addressRepository,
        CartRepositoryInterface $quoteRepository,
        QuoteManagement $quoteManagement,
        PaymentConfig $paymentConfig,
        Cart $cart,
        $customerShippingAddressId = null,
        $customerBillingAddressId = null,
        $shippingMethodCode = null,
        $paymentMethodCode = null
    ) {

        $this->addressRepository = $addressRepository;
        $this->quoteRepository = $quoteRepository;
        $this->quoteManagement = $quoteManagement;
        $this->paymentConfig = $paymentConfig;
        $this->cart = $cart;
        $this->customerShippingAddressId = $customerShippingAddressId;
        $this->customerBillingAddressId = $customerBillingAddressId;
        $this->shippingMethodCode = $shippingMethodCode;
        $this->paymentMethodCode = $paymentMethodCode;
    }

    public static function fromCart(Cart $cart, ObjectManagerInterface $objectManager = null) : CustomerCheckout
    {
        if ($objectManager === null) {
            $objectManager = Bootstrap::getObjectManager();
        }
        return new static(
            $objectManager->create(AddressRepositoryInterface::class),
            $objectManager->create(CartRepositoryInterface::class),
            $objectManager->create(QuoteManagement::class),
            $objectManager->create(PaymentConfig::class),
            $cart
        );
    }

    public function withCustomerBillingAddressId(int $addressId) : CustomerCheckout
    {
        $checkout = clone $this;
        $checkout->customerBillingAddressId = $addressId;
        return $checkout;
    }

    public function withCustomerShippingAddressId(int $addressId) : CustomerCheckout
    {
        $checkout = clone $this;
        $checkout->customerShippingAddressId = $addressId;
        return $checkout;
    }

    public function withShippingMethodCode(string $code) : CustomerCheckout
    {
        $checkout = clone $this;
        $checkout->shippingMethodCode = $code;
        return $checkout;
    }

    public function withPaymentMethodCode(string $code) : CustomerCheckout
    {
        $checkout = clone $this;
        $checkout->paymentMethodCode = $code;
        return $checkout;
    }

    /**
     * @return int Customer shipping address as configured or try default shipping address
     */
    private function getCustomerShippingAddressId() : int
    {
        return $this->customerShippingAddressId
            ?? $this->cart->getCustomerSession()->getCustomer()->getDefaultShippingAddress()->getId();
    }


    /**
     * @return int Customer billing address as configured or try default billing address
     */
    private function getCustomerBillingAddressId() : int
    {
        return $this->customerBillingAddressId
            ?? $this->cart->getCustomerSession()->getCustomer()->getDefaultBillingAddress()->getId();
    }

    /**
     * @return string Shipping method code as configured, or try first available rate
     */
    private function getShippingMethodCode() : string
    {
        return $this->shippingMethodCode
            ?? $this->cart->getQuote()->getShippingAddress()->getAllShippingRates()[0]->getCode();
    }

    /**
     * @return string Payment method code as configured, or try first available method
     */
    private function getPaymentMethodCode() : string
    {
        return $this->paymentMethodCode ?? array_values($this->paymentConfig->getActiveMethods())[0]->getCode();
    }

    public function placeOrder() : OrderInterface
    {
        $this->saveBilling();
        $this->saveShipping();
        $this->savePayment();
        /** @var Quote $reloadedQuote */
        $reloadedQuote = $this->quoteRepository->get($this->cart->getQuote()->getId());
        // Collect missing totals, like shipping
        $reloadedQuote->collectTotals();
        $order = $this->quoteManagement->submit($reloadedQuote);
        $this->cart->getCheckoutSession()->clearQuote();
        return $order;
    }

    private function saveBilling()
    {
        $billingAddress = $this->cart->getQuote()->getBillingAddress();
        $billingAddress->importCustomerAddressData(
            $this->addressRepository->getById($this->getCustomerBillingAddressId())
        );
        $billingAddress->save();
    }

    private function saveShipping()
    {
        $shippingAddress = $this->cart->getQuote()->getShippingAddress();
        $shippingAddress->importCustomerAddressData(
            $this->addressRepository->getById($this->getCustomerShippingAddressId())
        );
        $shippingAddress->setCollectShippingRates(true);
        $shippingAddress->collectShippingRates();
        $shippingAddress->setShippingMethod($this->getShippingMethodCode());
        $shippingAddress->save();
    }

    private function savePayment()
    {
        $payment = $this->cart->getQuote()->getPayment();
        $payment->setMethod($this->getPaymentMethodCode());
        $payment->save();
    }
}
