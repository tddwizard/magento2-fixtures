# TddWizard Fixture library

This library is in *alpha* state, that means:

- it's super incomplete
- nothing is guaranteed to work
- everything can still be changed


[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Code Climate](https://img.shields.io/codeclimate/maintainability/tddwizard/magento2-fixtures?style=flat-square)](https://codeclimate.com/github/tddwizard/magento2-fixtures)

---

## What is it?

An alternative to the procedural script based fixtures in Magento 2 integration tests.

It aims to be:

- extensible
- expressive
- easy to use

## Installation

Install it into your Magento 2 project with composer:

    composer require --dev tddwizard/magento2-fixtures

## Usage examples:

### Customer

If you need a customer without specific data, this is all:

```php
protected function setUp(): void
{
  $this->customerFixture = new CustomerFixture(
    CustomerBuilder::aCustomer()->build()
  );
}
protected function tearDown(): void
{
  $this->customerFixture->rollback();
}
```

It uses default sample data and a random email address. If you need the ID or email address in the tests, the `CustomerFixture` gives you access:

```php
$this->customerFixture->getId();
$this->customerFixture->getEmail();
```

You can configure the builder with attributes:

```php
CustomerBuilder::aCustomer()
  ->withEmail('test@example.com')
  ->withCustomAttributes(
    [
      'my_custom_attribute' => 42
    ]
  )
  ->build()
```

You can add addresses to the customer:

```php
CustomerBuilder::aCustomer()
  ->withAddresses(
    AddressBuilder::anAddress()->asDefaultBilling(),
    AddressBuilder::anAddress()->asDefaultShipping(),
    AddressBuilder::anAddress()
  )
  ->build()
```

Or just one:

```php
CustomerBuilder::aCustomer()
  ->withAddresses(
    AddressBuilder::anAddress()->asDefaultBilling()->asDefaultShipping()
  )
  ->build()
```

The `CustomerFixture` also has a shortcut to create a customer session:

```php
$this->customerFixture->login();
```



### Addresses

Similar to the customer builder you can also configure the address builder with custom attributes:

```php
AddressBuilder::anAddress()
  ->withCountryId('DE')
  ->withCity('Aachen')
  ->withPostcode('52078')
  ->withCustomAttributes(
    [
      'my_custom_attribute' => 42
    ]
  )
  ->asDefaultShipping()
```

### Product

Product fixtures work similar as customer fixtures:

```php
protected function setUp(): void
{
  $this->productFixture = new ProductFixture(
    ProductBuilder::aSimpleProduct()
      ->withPrice(10)
      ->withCustomAttributes(
        [
          'my_custom_attribute' => 42
        ]
      )
      ->build()
  );
}
protected function tearDown(): void
{
  $this->productFixture->rollback();
}
```

The SKU is randomly generated and can be accessed through `ProductFixture`, just as the ID:

```php
$this->productFixture->getSku();
$this->productFixture->getId();
```

### Cart/Checkout

To create a quote, use the `CartBuilder` together with product fixtures:

```php
$cart = CartBuilder::forCurrentSession()
  ->withSimpleProduct(
    $productFixture1->getSku()
  )
  ->withSimpleProduct(
    $productFixture2->getSku(), 10 // optional qty parameter
  )
  ->build()
$quote = $cart->getQuote();
```

Checkout is supported for logged in customers. To create an order, you can simulate the checkout as follows, given a customer fixture with default shipping and billing addresses and a product fixture:

```php
$this->customerFixture->login();
$checkout = CustomerCheckout::fromCart(
  CartBuilder::forCurrentSession()
    ->withSimpleProduct(
      $productFixture->getSku()
    )
    ->build()
);
$order = $checkout->placeOrder();

```

It will try to select the default addresses and the first available shipping and payment methods.

You can also select them explicitly:

```php
$order = $checkout
  ->withShippingMethodCode('freeshipping_freeshipping')
  ->withPaymentMethodCode('checkmo')
  ->withCustomerBillingAddressId($this->customerFixture->getOtherAddressId())
  ->withCustomerShippingAddressId($this->customerFixture->getOtherAddressId())
  ->placeOrder();
```

### Order

The `OrderBuilder` is a shortcut for checkout simulation.

```php
$order = OrderBuilder::anOrder()->build(); 
```

Logged-in customer, products, and cart item quantities will be
generated internally unless more control is desired:

```php
$order = OrderBuilder::anOrder()
    ->withProducts(
        // prepare catalog product fixtures
        ProductBuilder::aSimpleProduct()->withSku('foo'),
        ProductBuilder::aSimpleProduct()->withSku('bar')
    )->withCart(
        // define cart item quantities
        CartBuilder::forCurrentSession()->withSimpleProduct('foo', 2)->withSimpleProduct('bar', 3)
    )->build();
```

### Shipment

Orders can be fully or partially shipped, optionally with tracks.

```php
$order = OrderBuilder::anOrder()->build();

// ship everything
$shipment = ShipmentBuilder::forOrder($order)->build();
// ship only given order items, add tracks
$shipment = ShipmentBuilder::forOrder($order)
    ->withItem($fooItemId, $fooQtyToShip)
    ->withItem($barItemId, $barQtyToShip)
    ->withTrackingNumbers('123-FOO', '456-BAR')
    ->build();
```

### Invoice

Orders can be fully or partially invoiced.

```php
$order = OrderBuilder::anOrder()->build();

// invoice everything
$invoice = InvoiceBuilder::forOrder($order)->build();
// invoice only given order items
$invoice = InvoiceBuilder::forOrder($order)
    ->withItem($fooItemId, $fooQtyToInvoice)
    ->withItem($barItemId, $barQtyToInvoice)
    ->build();
```

### Credit Memo

Credit memos can be created for either all or some of the items ordered.
An invoice to refund will be created internally.

```php
$order = OrderBuilder::anOrder()->build();

// refund everything
$creditmemo = CreditmemoBuilder::forOrder($order)->build();
// refund only given order items
$creditmemo = CreditmemoBuilder::forOrder($order)
    ->withItem($fooItemId, $fooQtyToRefund)
    ->withItem($barItemId, $barQtyToRefund)
    ->build();
```

### Fixture pools

To manage multiple fixtures, **fixture pools** have been introduced for products and customers:

```
protected function setUp()
{
    $this->productFixtures = new ProductFixturePool;
    $this->customerFixtures = new CustomerFixturePool;
}

protected function tearDown()
{
    $this->productFixtures->rollback();
    $this->customerFixtures->rollback();
}

public function testSomethingWithMultipleProducts()
{
    $this->productFixtures->add(ProductBuilder::aSimpleProduct()->build());
    $this->productFixtures->add(ProductBuilder::aSimpleProduct()->build(), 'foo');
    $this->productFixtures->add(ProductBuilder::aSimpleProduct()->build());

    $this->productFixtures->get();      // returns ProductFixture object for last added product
    $this->productFixtures->get('foo'); // returns ProductFixture object for product added with specific key 'foo'
    $this->productFixtures->get(0);     // returns ProductFixture object for first product added without specific key (numeric array index)

}

public function testSomethingWithMultipleCustomers()
{
    $this->customerFixtures->add(CustomerBuilder::aCustomer()->build());
    $this->customerFixtures->add(CustomerBuilder::aCustomer()->build(), 'foo');
    $this->customerFixtures->add(CustomerBuilder::aCustomer()->build());

    $this->customerFixtures->get();      // returns CustomerFixture object for last added customer
    $this->customerFixtures->get('foo'); // returns CustomerFixture object for customer added with specific key 'foo'
    $this->customerFixtures->get(0);     // returns CustomerFixture object for first customer added without specific key (numeric array index)

}
```

## Credits

- [Fabian Schmengler][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.txt) for more information.

[ico-version]: https://img.shields.io/packagist/v/tddwizard/magento2-fixtures.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/tddwizard/magento2-fixtures/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/tddwizard/magento2-fixtures?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/tddwizard/magento2-fixtures.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/tddwizard/magento2-fixtures
[link-travis]: https://travis-ci.org/tddwizard/magento2-fixtures
[link-scrutinizer]: https://scrutinizer-ci.com/g/tddwizard/magento2-fixtures/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/tddwizard/magento2-fixtures
[link-author]: https://github.com/schmengler
[link-contributors]: ../../contributors

