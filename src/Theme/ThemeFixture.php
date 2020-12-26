<?php
declare(strict_types=1);

namespace TddWizard\Fixtures\Theme;

use Magento\Framework\View\DesignInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Theme\Model\Theme\Registration;

/**
 * A fixture to test theme features, e.g. with with test themes in `@magentoComponentsDir`
 */
class ThemeFixture
{

    /**
     * Register new themes from the `@magentoComponentsDir` fixture in the database
     */
    public static function registerTestThemes(): void
    {
        /** @var Registration $registration */
        $registration = Bootstrap::getObjectManager()->get(Registration::class);
        $registration->register();
    }

    /**
     * Set the current theme
     *
     * @param string $themePath a theme identifier without the area, e.g. Magento/luma
     */
    public static function setCurrentTheme(string $themePath): void
    {
        /** @var DesignInterface $design */
        $design = Bootstrap::getObjectManager()->get(DesignInterface::class);
        $design->setDesignTheme($themePath);
    }
}
