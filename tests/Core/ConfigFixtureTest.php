<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Core;

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class ConfigFixtureTest extends TestCase
{
    const         STORE_NAME_PATH              = 'general/store_information/name';
    private const SECOND_STORE_ID_FROM_FIXTURE = 'fixture_second_store';
    private const FIRST_STORE_ID               = 'default';
    /**
     * @var MutableScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->scopeConfig = $objectManager->get(MutableScopeConfigInterface::class);
        $this->storeManager = $objectManager->get(StoreManagerInterface::class);
    }

    public function testSetGlobalChangesDefaultScope()
    {
        ConfigFixture::setGlobal(self::STORE_NAME_PATH, 'Ye Olde Wizard Shop');
        $this->assertConfigValue(
            'Ye Olde Wizard Shop',
            self::STORE_NAME_PATH,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }

    /**
     * @magentoAppArea frontend
     */
    public function testSetGlobalOverridesAllScopes()
    {
        $this->givenStoreValue(self::STORE_NAME_PATH, 'Store Override');
        $this->givenWebsiteValue(self::STORE_NAME_PATH, 'Website Override');
        ConfigFixture::setGlobal(self::STORE_NAME_PATH, 'Global Value');
        $this->assertConfigValue('Global Value', self::STORE_NAME_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Store/_files/second_store.php
     */
    public function testSetForStoreWithCurrentStore()
    {
        $this->storeManager->setCurrentStore(self::SECOND_STORE_ID_FROM_FIXTURE);
        ConfigFixture::setForStore(self::STORE_NAME_PATH, 'Store store');
        $this->assertConfigValue(
            'Store store',
            self::STORE_NAME_PATH,
            ScopeInterface::SCOPE_STORE,
            self::SECOND_STORE_ID_FROM_FIXTURE
        );
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Store/_files/second_store.php
     */
    public function testSetForStoreWithExplicitStore()
    {
        ConfigFixture::setForStore(self::STORE_NAME_PATH, 'Store 1', self::FIRST_STORE_ID);
        ConfigFixture::setForStore(self::STORE_NAME_PATH, 'Store 2', self::SECOND_STORE_ID_FROM_FIXTURE);
        $this->assertConfigValue(
            'Store 1',
            self::STORE_NAME_PATH,
            ScopeInterface::SCOPE_STORE,
            self::FIRST_STORE_ID
        );
        $this->assertConfigValue(
            'Store 2',
            self::STORE_NAME_PATH,
            ScopeInterface::SCOPE_STORE,
            self::SECOND_STORE_ID_FROM_FIXTURE
        );
    }

    private function givenStoreValue(string $path, string $storeValue): void
    {
        $this->scopeConfig->setValue($path, $storeValue, ScopeInterface::SCOPE_STORE);
    }

    private function givenWebsiteValue(string $path, string $websiteValue): void
    {
        $this->scopeConfig->setValue($path, $websiteValue, ScopeInterface::SCOPE_WEBSITE);
    }

    private function assertConfigValue($expectedValue, string $path, string $scope, ?string $scopeCode = null): void
    {
        $this->assertEquals(
            $expectedValue,
            $this->scopeConfig->getValue($path, $scope, $scopeCode)
        );
    }
}
