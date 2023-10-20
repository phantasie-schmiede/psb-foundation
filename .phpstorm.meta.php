<?php

/**
 * Extend PhpStorms code completion capabilities by providing a meta file
 *
 * @link https://www.jetbrains.com/help/phpstorm/ide-advanced-metadata.html
 */

namespace PHPSTORM_META {

    expectedArguments(\TYPO3\CMS\Core\Context\Context::getAspect(), 0, 'backend.user', 'date', 'frontend.user',
        'language', 'typoscript', 'visibility', 'workspace');
    override(\TYPO3\CMS\Core\Context\Context::getAspect(), map([
        'backend.user'  => \TYPO3\CMS\Core\Context\UserAspect::class,
        'date'          => \TYPO3\CMS\Core\Context\DateTimeAspect::class,
        'frontend.user' => \TYPO3\CMS\Core\Context\UserAspect::class,
        'language'      => \TYPO3\CMS\Core\Context\LanguageAspect::class,
        'typoscript'    => \TYPO3\CMS\Core\Context\TypoScriptAspect::class,
        'visibility'    => \TYPO3\CMS\Core\Context\VisibilityAspect::class,
        'workspace'     => \TYPO3\CMS\Core\Context\WorkspaceAspect::class,
    ]));
    expectedArguments(\TYPO3\CMS\Core\Context\DateTimeAspect::get(), 0, 'accessTime', 'full', 'iso', 'timestamp',
        'timezone');
    expectedArguments(\TYPO3\CMS\Core\Context\VisibilityAspect::get(), 0, 'includeDeletedRecords',
        'includeHiddenContent', 'includeHiddenPages');
    expectedArguments(\TYPO3\CMS\Core\Context\UserAspect::get(), 0, 'groupIds', 'groupNames', 'id', 'isAdmin',
        'isLoggedIn', 'username');
    expectedArguments(\TYPO3\CMS\Core\Context\WorkspaceAspect::get(), 0, 'id', 'isLive', 'isOffline');
    expectedArguments(\TYPO3\CMS\Core\Context\LanguageAspect::get(), 0, 'contentId', 'fallbackChain', 'id',
        'legacyLanguageMode', 'legacyOverlayType', 'overlayType');
    expectedArguments(\TYPO3\CMS\Core\Context\TypoScriptAspect::get(), 0, 'forcedTemplateParsing');

    expectedArguments(\Psr\Http\Message\ServerRequestInterface::getAttribute(), 0, 'frontend.controller',
        'frontend.typoscript', 'frontend.user', 'language', 'module', 'moduleData', 'normalizedParams', 'routing',
        'site');
    override(\Psr\Http\Message\ServerRequestInterface::getAttribute(), map([
        'frontend.controller' => \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController::class,
        'frontend.typoscript' => \TYPO3\CMS\Core\TypoScript\FrontendTypoScript::class,
        'frontend.user'       => \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication::class,
        'language'            => \TYPO3\CMS\Core\Site\Entity\SiteLanguage::class,
        'module'              => \TYPO3\CMS\Backend\Module\ModuleInterface::class,
        'moduleData'          => \TYPO3\CMS\Backend\Module\ModuleData::class,
        'normalizedParams'    => \TYPO3\CMS\Core\Http\NormalizedParams::class,
        'routing'             => '\TYPO3\CMS\Core\Routing\SiteRouteResult|\TYPO3\CMS\Core\Routing\PageArguments',
        'site'                => \TYPO3\CMS\Core\Site\Entity\SiteInterface::class,
    ]));

    expectedArguments(\TYPO3\CMS\Core\Http\ServerRequest::getAttribute(), 0, 'frontend.user', 'language', 'module',
        'moduleData', 'normalizedParams', 'routing', 'site');
    override(\TYPO3\CMS\Core\Http\ServerRequest::getAttribute(), map([
        'frontend.user'    => \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication::class,
        'language'         => \TYPO3\CMS\Core\Site\Entity\SiteLanguage::class,
        'module'           => \TYPO3\CMS\Backend\Module\ModuleInterface::class,
        'moduleData'       => \TYPO3\CMS\Backend\Module\ModuleData::class,
        'normalizedParams' => \TYPO3\CMS\Core\Http\NormalizedParams::class,
        'routing'          => '\TYPO3\CMS\Core\Routing\SiteRouteResult|\TYPO3\CMS\Core\Routing\PageArguments',
        'site'             => \TYPO3\CMS\Core\Site\Entity\SiteInterface::class,
    ]));

    override(\TYPO3\CMS\Core\Routing\SiteMatcher::matchRequest(),
        type(\TYPO3\CMS\Core\Routing\SiteRouteResult::class, \TYPO3\CMS\Core\Routing\RouteResultInterface::class,));

    override(\TYPO3\CMS\Core\Routing\PageRouter::matchRequest(),
        type(\TYPO3\CMS\Core\Routing\PageArguments::class, \TYPO3\CMS\Core\Routing\RouteResultInterface::class,));

    override(\Psr\Container\ContainerInterface::get(0), map([
        '' => '@',
    ]));

    override(\Psr\EventDispatcher\EventDispatcherInterface::dispatch(0), map([
        '' => '@',
    ]));

    override(\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(0), type(0));
}
