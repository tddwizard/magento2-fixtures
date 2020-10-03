<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Core;

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;

class ConfigFixture
{

    /**
     * Sets configuration in default scope AND all stores, no matter what was configured previously
     *
     * @param string $path
     * @param mixed $value
     */
    public static function setGlobal(string $path, $value): void
    {
        self::scopeConfig()->setValue($path, $value, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        foreach (self::storeRepository()->getList() as $store) {
            self::scopeConfig()->setValue($path, $value, ScopeInterface::SCOPE_STORE, $store->getCode());
        }
    }

    /**
     * Sets configuration in store scope
     *
     * @param string $path
     * @param mixed $value
     * @param null $storeCode store code or NULL for current store
     */
    public static function setForStore(string $path, $value, $storeCode = null) : void
    {
        self::scopeConfig()->setValue($path, $value, ScopeInterface::SCOPE_STORE, $storeCode);
    }

    private static function scopeConfig(): MutableScopeConfigInterface
    {
        return Bootstrap::getObjectManager()->get(MutableScopeConfigInterface::class);
    }

    private static function storeRepository(): StoreRepositoryInterface
    {
        return Bootstrap::getObjectManager()->get(StoreRepositoryInterface::class);
    }
}
