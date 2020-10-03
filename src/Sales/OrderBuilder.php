<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Sales;

use Magento\Sales\Model\Order;
use TddWizard\Fixtures\Catalog\ProductBuilder;
use TddWizard\Fixtures\Checkout\CartBuilder;
use TddWizard\Fixtures\Checkout\CustomerCheckout;
use TddWizard\Fixtures\Customer\AddressBuilder;
use TddWizard\Fixtures\Customer\CustomerBuilder;
use TddWizard\Fixtures\Customer\CustomerFixture;

/**
 * Builder to be used by fixtures
 */
class OrderBuilder
{
    /**
     * @var CartBuilder
     */
    private $cartBuilder;

    /**
     * @var ProductBuilder[]
     */
    private $productBuilders;

    /**
     * @var CustomerBuilder
     */
    private $customerBuilder;

    /**
     * @var string
     */
    private $shippingMethod;

    final public function __construct()
    {
    }

    /**
     * @var string
     */
    private $paymentMethod;

    public static function anOrder(): OrderBuilder
    {
        return new static();
    }

    public function withProducts(ProductBuilder ...$productBuilders): OrderBuilder
    {
        $builder = clone $this;
        $builder->productBuilders = $productBuilders;

        return $builder;
    }

    public function withCustomer(CustomerBuilder $customerBuilder): OrderBuilder
    {
        $builder = clone $this;
        $builder->customerBuilder = $customerBuilder;

        return $builder;
    }

    public function withCart(CartBuilder $cartBuilder): OrderBuilder
    {
        $builder = clone $this;
        $builder->cartBuilder = $cartBuilder;

        return $builder;
    }

    public function withShippingMethod(string $shippingMethod): OrderBuilder
    {
        $builder = clone $this;
        $builder->shippingMethod = $shippingMethod;

        return $builder;
    }

    public function withPaymentMethod(string $paymentMethod): OrderBuilder
    {
        $builder = clone $this;
        $builder->paymentMethod = $paymentMethod;

        return $builder;
    }

    /**
     * @return Order
     * @throws \Exception
     */
    public function build(): Order
    {
        $builder = clone $this;

        if (empty($builder->productBuilders)) {
            // init simple products
            for ($i = 0; $i < 3; $i++) {
                $builder->productBuilders[] = ProductBuilder::aSimpleProduct();
            }
        }

        // create products
        $products = array_map(
            static function (ProductBuilder $productBuilder) {
                return $productBuilder->build();
            },
            $builder->productBuilders
        );

        if (empty($builder->customerBuilder)) {
            // init customer
            $builder->customerBuilder = CustomerBuilder::aCustomer()
                ->withAddresses(AddressBuilder::anAddress()->asDefaultBilling()->asDefaultShipping());
        }

        // log customer in
        $customer = $builder->customerBuilder->build();
        $customerFixture = new CustomerFixture($customer);
        $customerFixture->login();

        if (empty($builder->cartBuilder)) {
            // init cart, add products
            $builder->cartBuilder = CartBuilder::forCurrentSession();
            foreach ($products as $product) {
                $qty = 1;
                $builder->cartBuilder = $builder->cartBuilder->withSimpleProduct($product->getSku(), $qty);
            }
        }

        // check out, place order
        $checkout = CustomerCheckout::fromCart($builder->cartBuilder->build());
        if ($builder->shippingMethod) {
            $checkout = $checkout->withShippingMethodCode($builder->shippingMethod);
        }

        if ($builder->paymentMethod) {
            $checkout = $checkout->withPaymentMethodCode($builder->paymentMethod);
        }

        $order = $checkout->placeOrder();

        $customerFixture->logout();

        return $order;
    }
}
