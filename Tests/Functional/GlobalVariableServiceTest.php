<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Tests\Functional;

use PHPUnit\Framework\Attributes\Test;
use PSB\PsbFoundation\Service\GlobalVariableProviders\EarlyAccessConstantsProvider;
use PSB\PsbFoundation\Service\GlobalVariableProviders\RequestParameterProvider;
use PSB\PsbFoundation\Service\GlobalVariableProviders\SiteConfigurationProvider;
use PSB\PsbFoundation\Service\GlobalVariableService;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Tests\Functional\SiteHandling\SiteBasedTestTrait;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Class GlobalVariableServiceTest
 *
 * @package PSB\PsbFoundation\Tests\Functional
 */
class GlobalVariableServiceTest extends FunctionalTestCase
{
    use SiteBasedTestTrait;

    public const ROOT_PAGE_ID = 1;
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/psb/psb-foundation',
    ];

    #[Test]
    public function registeredProvidersAreAccessible(): void
    {
        self::assertTrue(GlobalVariableService::has(EarlyAccessConstantsProvider::class));
        self::assertTrue(GlobalVariableService::has(RequestParameterProvider::class));
        self::assertTrue(GlobalVariableService::has(SiteConfigurationProvider::class));
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/pages.csv');
        $this->mockRequest();
        $this->mockSiteConfiguration();
        $this->mockTsfe();
    }

    public function tearDown(): void
    {
        unset($GLOBALS['TSFE'], $GLOBALS['TYPO3_REQUEST']);
        parent::tearDown();
    }

    private function mockRequest(): void
    {
        $request = new ServerRequest('http://example.com/en/', 'GET', null, [],
            ['HTTP_HOST' => 'example.com', 'REQUEST_URI' => '/en/']);
        $GLOBALS['TYPO3_REQUEST'] = $request->withQueryParams(['id' => self::ROOT_PAGE_ID])
            ->withAttribute('normalizedParams', NormalizedParams::createFromRequest($request))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
    }

    private function mockSiteConfiguration(): void
    {
        $this->writeSiteConfiguration('tree_page_layout_test', $this->buildSiteConfiguration(self::ROOT_PAGE_ID, '/'));
    }

    private function mockTsfe(): void
    {
        $GLOBALS['TSFE'] = $this->getMockBuilder(TypoScriptFrontendController::class)
            ->disableOriginalConstructor()
            ->getMock();
        $GLOBALS['TSFE']->id = 1;
    }
}
