# PSB Foundation
## Enhanced extension programming in Extbase

- [What does it do?](#what-does-it-do)
- [Why should you use it?](#why-should-you-use-it)
- [Getting started](#getting-started)
- [TCA generation](#tca-generation)
- [Registering and configuring plugins](#registering-and-configuring-plugins)
- [Registering and configuring modules](#registering-and-configuring-modules)
- [Registering custom page types](#registering-custom-page-types)
- [Auto-registration of TypoScript-files](#auto-registration-of-typoscript-files)
- [Auto-registration of TSConfig-files](#auto-registration-of-tsconfig-files)
- [Auto-registration of icons](#auto-registration-of-icons)
- [Extension settings](#extension-settings)
- [Helper classes](#helper-classes)
  - [ContextUtility](#contextutility)
  - [GlobalVariableService](#globalvariableservice)
  - [StringUtility](#stringutility)
  - [TypoScriptProviderService](#typoscriptproviderservice)

### What does it do?
This extension
- generates the TCA for your domain models by reading its PHPDoc-annotations
- configures and registers modules and plugins based on PHPDoc-annotations in your controllers
- registers custom page types
- auto-registers existing FlexForms, TypoScript, TSConfig and icons
- provides convenient ways to access often used core-functionalities

### Why should you use it?
The goal of this extension is to
- bring together different points of configuration to improve readability, maintainability and development time
- allow code completion for TCA-settings
- reduce duplicated code
- reduce number of hard-coded string identifiers and keys, and therefore the likelihood of errors due to typos

The stronger use of convention over configuration simplifies the adaptation to breaking changes in those parts this extension handles.
Think about the updated registration of icons (`Configuration/Icons.php`) or the way plugin configuration has changed (remove vendor, add full qualified controller classname) in TYPO3 v11.
You wouldn't have had to refactor each and every extension of yours. Some small changes in psb_foundation and you are ready to go.

### Getting started
Create the following file: `EXT:your_extension/Classes/Data/ExtensionInformation.php`. Define the class and make
it extend `PSB\PsbFoundation\Data\AbstractExtensionInformation`.

Example:

```php
<?php
declare(strict_types=1);

/** copyright... */

namespace Vendor\ExtensionName\Data;

use PSB\PsbFoundation\Data\AbstractExtensionInformation;

class ExtensionInformation extends AbstractExtensionInformation
{
}
```

You can use inherited functions like `getExtensionKey()` or `getVendorName()` to get rid of hard-coded identifiers, if
you want to.

psb_foundation (don't forget to add it to your extension's dependencies) searches all active packages for the
file `EXT:your_extension/Classes/Data/ExtensionInformation.php` and checks if that class implements
the `PSB\PsbFoundation\Data\ExtensionInformationInterface`. All extensions that meet these requirements are taken into
account during automated configuration processes, e.g. during TCA generation or icon registration.

### TCA generation
You don't need to create a special file for your domain model in `Configuration/TCA` anymore!
psb_foundation will scan your `Classes/Domain/Model`-directory for all classes (skipping abstract ones) that have an
annotation of type `PSB\PsbFoundation\Annotation\TCA\Ctrl` in their PHPDoc-comment. The script checks if your model
relates to an existing table in the database and detects if it extends another model from a different extension and
manipulates the TCA accordingly.

You can provide configuration options via PHPDoc-annotations. Available annotations with default values can be found
in `psb_foundation/Classes/Annotations/TCA`. There are more annotations than unique field types. This extension offers
different presets, e.g.

* type: input
    * Date
    * DateTime
    * Input
    * Integer
    * Link
* type: inline (FileReference)
    * Document
    * Image

Simple example:

```php
use PSB\PsbFoundation\Annotation\TCA;

/**
 * @TCA\Ctrl(label="name", searchFields="description, name")
 */
class YourClass
{
    /**
     * @var string
     * @TCA\Input(eval="trim, required")
     */
    protected string $name = '';

    /**
     * You can leave out the brackets if you are fine with the default values.
     *
     * @var string
     * @TCA\Input
     */
    protected string $inputUsingTheDefaultValues = '';

    /**
     * @var bool
     * @TCA\Checkbox(exclude=1)
     */
    protected bool $adminOnly = true;

    ...
}
```

Properties without TCA\[...]-annotation will not be considered in TCA-generation. The available annotation properties
also include onChange, label, position, etc.

The relational types inline, mm and select have a special property named `linkedModel`. Instead of using foreignTable
you can specify the class name of the related domain model and psb_foundation will insert the corresponding table name
into the TCA.

Additionally, the properties `foreignField` and `mmOppositeField` accept property names. These will be converted to
column names.

Extended example:

```php
use PSB\PsbFoundation\Annotation\TCA;

/**
 * @TCA\Ctrl(hideTable=true, label="richText", labelAlt="someProperty, anotherProperty",
 *           labelAltForce=true, sortBy="customSortField", type="myType")
 */
class YourModel
{
    /**
     * This constant is used as value for TCA-property "items". The keys will be used as label identifiers - converted
     * to lowerCamelCase:
     * [...]/table_of_your_model.xlf:myType.default
     * [...]/table_of_your_model.xlf:myType.recordWithImage
     */
    public const TYPES = [
        'DEFAULT'           => 'default',
        'RECORD_WITH_IMAGE' => 'recordWithImage',
    ];

    /**
     * @var string
     * @TCA\Select(items=self::TYPES)
     */
    protected string $myType = self::TYPES['DEFAULT'];

    /**
     * @var string
     * @TCA\Text(cols=40, enableRichtext=true, rows=10)
     */
    protected string $richText = '';

    /**
     * @var ObjectStorage<AnotherModel>
     * @TCA\Inline(linkedModel=AnotherModel::class, foreignField="yourModel")
     */
    protected ObjectStorage $inlineRelation;

    /**
     * @var AnotherModel|null
     * @TCA\Select(linkedModel=AnotherModel::class, position="before:propertyName2")
     */
    protected ?AnotherModel $simpleSelectField = null;

    /**
     * @var FileReference|null
     * @TCA\Image(allowedFileTypes="jpeg, jpg", maxItems=1, typeList="recordWithImage")
     */
    protected ?FileReference $image = null;

    ...
}
```

#### Extending domain models
When you are extending domain models (even from extensions that don't make use of psb_foundation) you have to add the
@TCA\Ctrl-annotation. You have the possibility to override ctrl-settings. If you don't want to override anything: just
leave out the brackets. The default values of the annotation class will have no effect in this case.

#### Default language label paths and additional configuration options
* If you don't provide a title in the Ctrl-annotation, this path will be
  tried: `EXT:your_extension/Resources/Private/Language/Backend/Configuration/TCA/(Overrides/)[table_name].xlf:ctrl.title`
* If you don't provide a label for a property, this path will be
  tried: `EXT:your_extension/Resources/Private/Language/Backend/Configuration/TCA/(Overrides/)[table_name].xlf:[propertyName]`
* You can add new tabs (will be transformed to `--div--;...`) by setting position to `tab:[identifier]`. `[identifier]`
  may be a full `LLL:`-path. If following label exists, this will be
  used: `EXT:your_extension/Resources/Private/Language/Backend/Configuration/TCA/(Overrides/)[table_name].xlf:tab.[identifier]`
* When you use the items-property for a select field, you may provide a simple associative array. It will be transformed
  into the required multi-level format. The labels will be build this
  way: `EXT:your_extension/Resources/Private/Language/Backend/Configuration/TCA/(Overrides/)[table_name].xlf:[propertyName].[arrayKeyTransformedToLowerCamelCase]`

### Registering and configuring plugins
- Classes/Data/ExtensionInformation.php
  ```php
  public const PLUGINS = [
      'PluginName' => [
          \Your\Extension\Controller\YourController::class,
      ],
      'AnotherPluginWithMultipleControllers' => [
          \Your\Extension\Controller\AnotherController::class,
          \Your\Extension\Controller\ExtraController::class,
          ...
      ],
      'AnotherPluginWhichDoesNotUseAllActionsInController' => [
          \Your\Extension\Controller\AnotherController::class,
          \Your\Extension\Controller\ExtraController::class => [
              'actionName1',
              'actionName2',
              ...
          ],
          ...
      ],
      ...
  ];
  ```

- Classes/Controller/YourController.php
  ```php
  use PSB\PsbFoundation\Annotation\PluginAction;
  use PSB\PsbFoundation\Annotation\PluginConfig;

  /**
   * @PluginConfig(iconIdentifier="special-icon")
   */
  class FirstController extends ActionController
  {
      /**
       * @PluginAction()
       */
      public function simpleAction(): void
      {
      }

      /**
       * @PluginAction(default=true)
       */
      public function mainAction(): void
      {
      }

      /**
       * @PluginAction(uncached=true)
       */
      public function uncachedAction(): void
      {
      }
  }
  ```

The `PluginConfig`-annotation is not mandatory. You only need to set it, when additional configuration is desired (e.g.
setting a custom icon identifier).
Actions without the `PluginAction`-annotation won't be registered though -
even if mentioned in the optional action list in `ExtensionInformation.php`!
If no action list is provided, all actions annotated with `PluginAction` will be registered.
Check the default values and comments in `EXT:psb_foundation/Classes/Annotation/`.

#### FlexForms
If there is a file named `EXT:your_extension/Configuration/FlexForms/[PluginName].xml` it will be registered
automatically. You can override this default by setting the `flexForm`-property of the `PluginConfig`-annotation. You
can either provide a filename if your XML-file is located inside the `Configuration/FlexForms/`-directory or a full file
path beginning with `EXT:`.

#### Content element wizard
Plugins will be added to the wizard automatically. There will be a tab for each vendor. You can override the location of
your wizard entry by setting the `group`-property of the `PluginConfig`-annotation. The following language labels are
taken into account automatically if defined:

- `EXT:your_extension/Resources/Private/Language/Backend/Configuration/TSConfig/Page/wizard.xlf:`
    * \[group\].elements.\[pluginName\].description
    * \[group\].elements.\[pluginName\].title

[group] defaults to the vendor name (lowercase) if not set within `PluginConfig`-annotation. That also defines the tab
of the content element wizard. If a new tab is created, its label will be fetched from
here: `EXT:your_extension/Resources/Private/Language/Backend/Configuration/TSConfig/Page/wizard.xlf:[group].header`

### Registering and configuring modules
This process is very similar to the way plugins are registered.

- Classes/Data/ExtensionInformation.php
  ```php
  public const MODULES = [
      'ModuleName' => [
          \Your\Extension\Controller\YourModuleController::class,
      ],
      ...
  ];
  ```

- Classes/Controller/YourModuleController.php
  ```php
  use PSB\PsbFoundation\Annotation\ModuleAction;
  use PSB\PsbFoundation\Annotation\ModuleConfig;

  /**
   * @ModuleConfig(iconIdentifier="special-icon", mainModuleName="web", position="after:list")
   */
  class YourModuleController extends ActionController
  {
      /**
       * @ModuleAction()
       * @return ResponseInterface
       */
      public function simpleAction(): ResponseInterface
      {
      }

      /**
       * @ModuleAction(default=true)
       * @return ResponseInterface
       */
      public function mainAction(): ResponseInterface
      {
      }
  }
  ```

Instead of `ActionController` you can extend `PSB\PsbFoundation\Controller\Backend\AbstractModuleController`. This class
provides the required `ModuleTemplateFactory`.
At the end of your actions you can just call its render()-function and your corresponding fluid-template will be returned.

```php
use PSB\PsbFoundation\Annotation\ModuleAction;
use PSB\PsbFoundation\Annotation\ModuleConfig;
use PSB\PsbFoundation\Controller\Backend\AbstractModuleController;

/**
* @ModuleConfig(iconIdentifier="special-icon", mainModuleName="web", position="after:list")
*/
class YourModuleController extends AbstractModuleController
{
   /**
     * @ModuleAction(default=true)
     * @return ResponseInterface
     */
    public function simpleAction(): ResponseInterface
    {
        return $this->render();
    }
}
```

### Registering custom page types
Classes/Data/ExtensionInformation.php
  ```php
  public const PAGE_TYPES = [
      123 => [ // doktype serves as key
          'allowedTables'  => ['*'],
          'iconIdentifier' => 'page-type-your-page-type-name'
          'label'          => 'Your page type name'
          'name'           => 'yourPageTypeName',
          'type'           => 'web',
      ],
      ...
  ];
  ```

The keys (doktype) have to be of type integer. `name` is the only mandatory value.
If you don't provide an icon identifier this default identifier will be used: `page-type-your-page-type-name`.
The identifier is also used as base for further icon-variants.

Example (`'name' => 'custom'`):
- page-type-custom
- page-type-custom-contentFromPid
- page-type-custom-hideinmenu
- page-type-custom-root

You don't have to provide all these icons. The icons for regular pages will be used as fallback.
Your SVG-files should to be located in this directory: `EXT:your_extension/Resources/Public/Icons/`
All icons in that directory will be registered by their name automatically.
Unless `label` is defined, `EXT:your_extension/Resources/Private/Language/Backend/Configuration/TCA/Overrides/pages.xlf:pageType.yourPageTypeName` will be used.
If that key doesn't exist, `name` will be transformed from "yourPageTypeName" to "Your page type name".

### Auto-registration of TypoScript-files
If there are `.typoscript`-files located in `EXT:your_extension/Configuration/TypoScript]`, psb_foundation will execute `\PSB\PsbFoundation\Utility\TypoScript\TypoScriptUtility::registerTypoScript()` for that directory.
You can provide a custom title for the select item in the template module with `EXT:your_extension/Resources/Private/Language/Backend/Configuration/TCA/Overrides/sys_template.xlf:template.title` -
defaults to `'Main configuration'`.

### Auto-registration of TSConfig-files
If these files exist, they will be included automatically:
- `EXT:your_extension/Configuration/TSConfig/Page.tsconfig`
- `EXT:your_extension/Configuration/TSConfig/User.tsconfig`

### Auto-registration of icons
All PNG- and SVG-files located in `EXT:your_extension/Resources/Public/Icons` will be registered automatically.
The extension key and the file name are used as icon identifier.

Examples (filename => icon identifier):
- `logo.svg` => `your-extension-key-logo`
- `MainMenu.png` => `your-extension-key-main-menu`

### Extension settings
#### Log missing language labels
If activated, missing language labels will be stored in tx_psbfoundation_missing_language_labels. This is restricted
to `PSB\PsbFoundation\Service\LocalizationService` which extends the LocalizationService of the TYPO3 core. All missing
default labels (e.g. plugin title or field label) will be listed this way if you didn't provide a custom label. Fixed
entries get removed on next check (every time the cache is cleared).
It's recommended to check this table during extension development.

### Helper classes
#### ContextUtility
`PSB\PsbFoundation\Utility\ContextUtility` offers short and easy access to basic information.
- getCurrentLocale
- isBackend
- isBootProcessRunning
- isFrontend
- isTypoScriptAvailable

#### GlobalVariableService
`PSB\PsbFoundation\Service\GlobalVariableService` allows easy and performant access to often needed data.
This service is a container where data providers can be registered by their class name.
Providers are instantiated only when they are accessed (and then only once).
Data that no longer changes can be cached within the GlobalVariableService for the current request.

It's possible to register own providers that implement `PSB\PsbFoundation\Service\GlobalVariableProviders\GlobalVariableProviderInterface`.
You can extend `PSB\PsbFoundation\Service\GlobalVariableProviders\AbstractProvider`.
Three providers are shipped within this extension.
- `PSB\PsbFoundation\Service\GlobalVariableProviders\RequestParameterProvider`: returns processed GET and POST parameters. The two arrays are merged (POST would overwrite GET) and all values are sanitized first and then converted by StringUtility::convertString().
- `PSB\PsbFoundation\Service\GlobalVariableProviders\SiteConfigurationProvider`: returns the SiteConfiguration using the SiteFinder.
- `PSB\PsbFoundation\Service\GlobalVariableProviders\EarlyAccessConstantsProvider`: gives you the possibility to define constants that you can access very early during bootstrap - before TypoScript is loaded. Have a look at the comments in this class!

Examples:
```php
// get SiteConfiguration
GlobalVariableService::get(SiteConfigurationProvider::class);

// get request parameters
GlobalVariableService::get(RequestParameterProvider::class);

// get specific request parameter
GlobalVariableService::get(RequestParameterProvider::class . '.formData.hiddenInput');
```

#### StringUtility
`PSB\PsbFoundation\Utility\StringUtility` contains some string manipulation functions, e.g.:
- convertString: performs a type cast or other operations based on the string's content, e.g.
  - `''`: empty string will be returned as empty string or null (depends on second argument)
  - `0`, `123`: returns integer
  - `0.1`, `0,1`: returns float
  - `0001423`: returns unchanged string
  - `TS:config.headerComment`: returns value from TypoScript (if path is valid) which is also processed by this function.
  - `\Full\Qualified\ClassName::CONSTANT['arrayKey']`: returns value of constant which is also processed by this function.
  - `{...}`: returns array if valid JSON
  - `false`, `true`: returns boolean
  - returns the string if no other format could be identified
- explodeByLineBreaks: can be used to get the lines of a file or text field as array
- getNumberFormatter: returns a NumberFormatter based on the current locale

#### TypoScriptProviderService
`PSB\PsbFoundation\Service\TypoScriptProviderService` offers a convenient way to retrieve specific TypoScript-settings.
As first argument you can provide the array path you want to access. The following arguments are the same as for the ConfigurationManager known from Extbase.

Examples:
```php
// get all TypoScript
$this->typoScriptProviderService->get();

// get specific part
$this->typoScriptProviderService->get('config');

// get all settings from extension
$this->typoScriptProviderService->get(null, ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'extensionName', 'pluginName');

// get specific setting from extension
$this->typoScriptProviderService->get('displayOptions.showPreview', ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'extensionName', 'pluginName');
```
The values returned by this service have been converted to the correct type already (using StringUtility::convertString()).
If you set `displayOptions.showPreview = 1` the last example will return an integer.
If you set `displayOptions.showPreview = true` it will return a boolean.
Not set constants will be returned as null.
