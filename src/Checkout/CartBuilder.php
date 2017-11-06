<?php

namespace TddWizard\Fixtures\Checkout;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
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

    public function __construct(ProductRepositoryInterface $productRepository, Cart $cart)
    {
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->addToCartRequests = [];
    }

    public static function forCurrentSession(ObjectManagerInterface $objectManager = null)
    {
        if ($objectManager === null) {
            $objectManager = Bootstrap::getObjectManager();
        }
        return new static(
            $objectManager->create(ProductRepositoryInterface::class),
            $objectManager->create(Cart::class)
        );
    }

    public function withSimpleProduct($sku, $qty = 1) : CartBuilder
    {
        $result = clone $this;
        $result->addToCartRequests[$sku][] = new DataObject(['qty' => $qty]);
        return $result;
    }

    /**
     * Lower-level API to support arbitary products
     *
     * @param $sku
     * @param int $qty
     * @param $request
     * @return CartBuilder
     */
    public function withProductRequest($sku, $qty = 1, $request = []) : CartBuilder
    {
        $result = clone $this;
        $requestInfo = array_merge(['qty'=> $qty], $request);
        $result->addToCartRequests[$sku][] = new DataObject($requestInfo);
        return $result;
    }

    public function build() : Cart
    {
        foreach ($this->addToCartRequests as $sku => $requests) {
            /** @var $product \Magento\Catalog\Model\Product */
            $product = $this->productRepository->get($sku);
            foreach ($requests as $requestInfo) {
                $this->cart->addProduct($product, $requestInfo);
            }
        }
        $this->cart->save();
        return $this->cart;
    }
}
