# TddWizard Fixture library

This library is in *alpha* state, that means:

- it's super incomplete
- nothing is guaranteed to work
- everything can still be changed

[![Wercker Status](https://app.wercker.com/status/c69205aea9e9617e027d2aa8d5a1817c/s/master "wercker status")](https://app.wercker.com/project/byKey/c69205aea9e9617e027d2aa8d5a1817c)
[![Code Climate](https://img.shields.io/codeclimate/github/tddwizard/magento2-fixtures.svg)](https://codeclimate.com/github/tddwizard/magento2-fixtures)
[![Latest Version](https://img.shields.io/packagist/v/tddwizard/magento2-fixtures.svg)](https://packagist.org/packages/tddwizard/magento2-fixtures)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)

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
protected function setUp()
{
  $this->customerFixture = new CustomerFixture(
    CustomerBuilder::aCustomer()->build()
  );
}
protected function tearDown()
{
  CustomerFixtureRollback::create()->execute($this->customerFixture);
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



### Adresses

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
protected function setUp()
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
protected function tearDown()
{
  ProductFixtureRollback::create()->execute($this->productFixture);
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
$order = OrderBuilder::anOrder()->withProducts(
    ProductBuilder::aSimpleProduct()->withSku('foo'),
    ProductBuilder::aSimpleProduct()->withSku('bar')
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
