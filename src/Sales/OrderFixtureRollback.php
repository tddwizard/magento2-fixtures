<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Sales;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @internal Use OrderFixture::rollback() or OrderFixturePool::rollback() instead
 */
class OrderFixtureRollback
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        Registry $registry,
        OrderRepository $orderRepository,
        CustomerRepositoryInterface $customerRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->registry = $registry;
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
    }

    public static function create(): OrderFixtureRollback
    {
        $objectManager = Bootstrap::getObjectManager();

        return new self(
            $objectManager->get(Registry::class),
            $objectManager->get(OrderRepositoryInterface::class),
            $objectManager->get(CustomerRepositoryInterface::class),
            $objectManager->get(ProductRepositoryInterface::class)
        );
    }

    /**
     * Roll back orders with associated customers and products.
     *
     * @param OrderFixture ...$orderFixtures
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(OrderFixture ...$orderFixtures): void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        foreach ($orderFixtures as $orderFixture) {
            $orderItems = $this->orderRepository->get($orderFixture->getId())->getItems();

            $this->orderRepository->deleteById($orderFixture->getId());
            $this->customerRepository->deleteById($orderFixture->getCustomerId());
            array_walk(
                $orderItems,
                function (OrderItemInterface $orderItem) {
                    try {
                        $this->productRepository->deleteById($orderItem->getSku());
                    } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                        // ignore if already deleted
                    }
                }
            );
        }

        $this->registry->unregister('isSecureArea');
    }
}
