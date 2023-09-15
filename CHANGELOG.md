PSB Foundation 2.0
==================

When I started this project, I promised that TYPO3 upgrades will become easier because the abstraction layer this
extension offers will remain and all core changes will be tackled under the hood so that you won't have to worry.
Guess what: As the developers of TYPO3 introduced a ton of breaking changes in v12.0 (which I love!) - and with php 8.1
at hand - I decided to go for a big refactoring. Sorry for that! But I hope that you will love the new structure and
features. Version 1 won't be supported anymore. The following list will guide you through all necessary steps.
I will try to support two major versions in the future.
Thank you for using psb_foundation! ❤

Breaking changes
----------------

- Raised minimum required versions - no backwards compatibility!
  - php version from 7.4 to 8.1
  - TYPO3 version from 11 to 12
- Method signature from ArrayUtility::inArrayRecursive() changed.
  - Argument `$returnIndex` is removed.
  - New argument `$searchKey` which allows to search for array keys instead of values.
  - Method always returns an array of paths with all occurences of the search value.
  - If nothing is found an empty array is returned.
  - New signature: `inArrayRecursive(array $haystack, mixed $needle, bool  $searchKey = false, bool  $searchForSubstring = false): array`
- FilePathUtility broke when using symlinks. Method signature and name of getLanguageFilePath() were changed with this
  refactoring!
  - changed to `getLanguageFilePathForCurrentFile(ExtensionInformationInterface $extensionInformation, string $filename = null)`
- Migrated annotation classes to php attributes.
  - Example: `/** @Column\Input(eval="trim") */` becomes `#[Column\Input(eval: 'trim')]`
  - Changed class name(!): Checkbox is now Check according to the underlying TCA type.
    - Render type of Check defaults to "default" now! (Old default was checkboxToggle.)
- Moved ajax page type configuration from action to plugin level (configuring a specific action is not supported by TYPO3
  anymore).
  - All actions, that need to be called via typeNum, have to be default actions. This might require the configuration of new plugins.
- Added mapping attributes and auto configuration for classes which make use of them.
  - TCA generation can't access the ClassesConfiguration from TYPO3 anymore because that uses the CacheManager which isn't available at this point.
    That means that no information from `Configuration/Extbase/Persistence/Classes.php` can be used.
    If this file exists in your extension you have to move all table and field mappings to the new attribute classes:
    - `Classes/Attribute/TCA/Mapping/Field` property attribute
    - `Classes/Attribute/TCA/Mapping/Table` class attribute
  - That information can be removed from Classes.php then.
    It will be generated automatically.
- Refactored registration of modules, page types and plugins.
  - Instead of the constants, use the new configuration classes inside the constructor of your `ExtensionInformation.php`:
    - `\PSB\PsbFoundation\Data\MainModuleConfiguration`
    - `\PSB\PsbFoundation\Data\ModuleConfiguration`
    - `\PSB\PsbFoundation\Data\PageTypeConfiguration`
    - `\PSB\PsbFoundation\Data\PluginConfiguration`
  - Example:
    ```php
    public function __construct()
    {
        parent::__construct();
        $this->addModule(GeneralUtility::makeInstance(ModuleConfiguration::class,
            controllers: [MyController::class],
            key: $this->buildModuleKeyPrefix() . 'my_module',
            parentModule: 'web'
        ));
    }
    ```
- Removed automatic inclusion of PageTSconfig as this is done by the core now. ([Feature #96614](https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Feature-96614-AutomaticInclusionOfPageTsConfigOfExtensions.html))
  - You might have to rename your TSconfig files.
- Changed required location of UserTSconfig files for automatic inclusion (following the core):
  - from `EXT:your_extension/Configuration/TsConfig/User/User.tsconfig` to `EXT:your_extension/Configuration/User.tsconfig` (You can use a lowercase filename, too.)
- Removed property injection traits!
  - Use constructor injection instead.
- Removed obsolete functions from StringUtility. Replace them with native php functions.
  - `StringUtility::beginsWith()` becomes `str_starts_with()`
  - `StringUtility::endsWith()` becomes `str_ends_with()`
- Removed static functions from RequestParameterProvider (getRequestParameter, getRequestParameters). 
  Use GlobalVariableService instead:
  ```php
  // get all request parameters
  GlobalVariableService::get(RequestParameterProvider::class);
  
  // get specific request parameter
  GlobalVariableService::get(RequestParameterProvider::class . '.formData.hiddenInput');
  ```
- Use new TCA-option `['ctrl']['security']['ignorePageTypeRestriction']` for allowing records on standard pages.
  - You can use the property `ignorePageTypeRestriction` of the Ctrl-attribute.

Features
--------

- Service for file uploads in frontend
  - uploads files and creates references to a given domain model record
  - configurable via TCA
- Attributes for new TCA types introduced in v12
- More properties and getters for TCA attributes
- Support of plural forms in language files
- Support of convenient placeholders in language files
- New attribute for TranslateViewHelper
  - excludedLanguages: matching language keys will return null (bypasses fallbacks)
- New helper functions to FileUtility
  - `getMimeType()` // based on finfo
  - `resolveFileName()` // resolves `EXT:`, but leaves invalid paths untouched (in contrast to `GeneralUtility::getFileAbsFileName()`)
  - `write()` // wrapper for file_put_contents which creates the file if it does not exist (including directories) and assures correct access rights
- Fallbacks for GlobalVariableService::get()
  - The method no longer throws an exception if a path does not exist and strict mode is set to false.
  - The fallback value can be overridden.

Bugfixes
--------

- Default action was not respected for the order of uncached actions in plugin configuration.
- Handle missing attributes correctly.
- Remove backslash from generated TypoScript object name when controller is inside additional subdirectories.
- Remove CacheManager dependencies from early used services.

Important
---------
- Custom TranslateViewHelper no longer extends original (which is final now).
- Remove CSH-registration (has also been removed from TYPO3 core).
  - All CSH-files can be deleted. You may want to transfer helpful texts to the 'description' field in TCA.
