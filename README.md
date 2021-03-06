# PSB Foundation
## Enhanced extension programming in Extbase

- [What does it do?](#what-does-it-do)
- [Why should you use it?](#why-should-you-use-it)
- [Getting started](#getting-started)
- [TCA generation](#tca-generation)
  - [Tabs and palettes](#tabs-and-palettes)
  - [Extending domain models](#extending-domain-models)
  - [Default language label paths](#default-language-label-paths)
  - [Additional configuration options](#additional-configuration-options)
- [Registering and configuring plugins](#registering-and-configuring-plugins)
  - [FlexForms](#flexforms)
  - [Content element wizard](#content-element-wizard)
  - [custom page types for single actions](#custom-page-types-for-single-actions)
- [Registering and configuring modules](#registering-and-configuring-modules)
- [Registering custom page types](#registering-custom-page-types)
- [Auto-registration of TypoScript-files](#auto-registration-of-typoscript-files)
- [Auto-registration of TSconfig-files](#auto-registration-of-tsconfig-files)
- [Auto-registration of icons](#auto-registration-of-icons)
- [Extension settings](#extension-settings)
- [Helper classes](#helper-classes)
  - [ContextUtility](#contextutility)
  - [GlobalVariableService](#globalvariableservice)
  - [StringUtility](#stringutility)
  - [TypoScriptProviderService](#typoscriptproviderservice)

### What does it do?
This extension
- generates the TCA for your domain models by reading their PHPDoc-annotations
- autocompletion for TCA by annotation classes
- configures and registers modules and plugins based on PHPDoc-annotations in your controllers
- registers custom page types
- auto-registers existing FlexForms, TypoScript, TSconfig, CSH language files and icons
- provides convenient ways to access often used core-functionalities

#### IMPORTANT
psb_foundation is designed to simplify common recurrent settings. Although it offers a versatile set of configurations
and tools, you may not be able to map certain complex scenarios with it.
But you can still make use of this extension.
For example, you can use the auto-generation of the TCA and add special settings via files in `TCA/Overrides/` as usual.

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

You can provide configuration options via PHPDoc-annotations.
Available annotations with default values can be found in `psb_foundation/Classes/Annotations/TCA`.
The available annotation properties also include onChange, label, position, etc.
There are more annotations than unique field types.
This extension offers different presets, e.g.

- type: input
  - Date
  - DateTime
  - Input
  - Integer
  - Link
- type: inline (FileReference)
  - Document
  - Image
- type: select
  - Inline
  - MM

Simple example:

```php
use PSB\PsbFoundation\Annotation\TCA\Column;
use PSB\PsbFoundation\Annotation\TCA\Ctrl;

/**
 * @Ctrl(label="name", searchFields="description, name")
 */
class YourClass
{
    /**
     * @Column\Input(eval="trim, required")
     */
    protected string $name = '';

    /**
     * You can leave out the brackets if you are fine with the default values.
     *
     * @Column\Input
     */
    protected string $inputUsingTheDefaultValues = '';

    /**
     * @Column\Checkbox(exclude=1)
     */
    protected bool $adminOnly = true;

    ...
}
```

Properties without TCA\[...]-annotation will not be considered in TCA-generation.
Some configurations will be added automatically if specific fields are defined in the ctrl-section:

| Property                  | Default value    |
|---------------------------|------------------|
| enableColumns > disabled  | hidden           |
| enableColumns > endtime   | endtime          |
| enableColumns > starttime | starttime        |
| languageField             | sys_language_uid |
| transOrigDiffSourceField  | l10n_diffsource  |
| transOrigPointerField     | l10n_parent      |
| translationSource         | l10n_source      |

Most of these properties will be added in additional tabs at the end of `showitems` for all types.

The relational types `inline`, `mm` and `select` have a special property named `linkedModel`.
Instead of using `foreign_table` you can specify the class name of the related domain model and psb_foundation will
insert the corresponding table name into the TCA.

Additionally, the properties `foreign_field` and `mm_opposite_field` accept property names.
These will be converted to column names.

Extended example:

```php
use PSB\PsbFoundation\Annotation\TCA\Column;
use PSB\PsbFoundation\Annotation\TCA\Ctrl;

/**
 * @Ctrl(hideTable=true, label="richText", labelAlt="someProperty, anotherProperty",
 *       labelAltForce=true, sortBy="customSortField", type="myType")
 */
class YourModel
{
    /**
     * This constant is used as value for TCA-property "items". The keys will be used as label identifiers - converted
     * to lowerCamelCase:
     * EXT:your_extension/Resources/Private/Language/Backend/Configuration/TCA/(Overrides/)[modelName].xlf:myType.default
     * EXT:your_extension/Resources/Private/Language/Backend/Configuration/TCA/(Overrides/)[modelName].xlf:myType.recordWithImage
     */
    public const TYPES = [
        'DEFAULT'           => 'default',
        'RECORD_WITH_IMAGE' => 'recordWithImage',
    ];

    /**
     * @Column\Select(items=self::TYPES)
     */
    protected string $myType = self::TYPES['DEFAULT'];

    /**
     * @Column\Text(cols=40, enableRichtext=true, rows=10)
     */
    protected string $richText = '';

    /**
     * @var ObjectStorage<AnotherModel>
     * @Column\Inline(linkedModel=AnotherModel::class, foreignField="yourModel")
     */
    protected ObjectStorage $inlineRelation;

    /**
     * @Column\Select(linkedModel=AnotherModel::class, position="before:propertyName2")
     */
    protected ?AnotherModel $simpleSelectField = null;

    /**
     * This field is only added to the types 'interview' and 'profile'.
     *
     * @Column\Image(allowedFileTypes="jpeg, jpg", maxItems=1, typeList="interview, profile")
     */
    protected ?FileReference $image = null;

    ...
}
```
#### Tabs and palettes
The argument position allows you to assign a field to a specific tab or palette.

Example:
```php
/**
 * @Column\Input(position="palette:my_palette")
 */
protected string $description = '';

/**
 * @Column\Input(position="tab:my_tab")
 */
protected string $note = '';
```
Without further configuration, tab and palette will be registered with the given identifier and added to the
end of the showitems-list. But you can add additional information for tabs and palettes.
For now, the identifiers of palettes and tabs have to be written in snake_case!

```php
use PSB\PsbFoundation\Annotation\TCA;
use PSB\PsbFoundation\Annotation\TCA\Column;

/**
 * @TCA\Ctrl()
 * @TCA\Palette(description="LLL:EXT:[...]", identifier="my_palette", position="before:someProperty")
 * @TCA\Tab(identifier="my_tab", label="Custom label", position="after:someProperty")
 */
class YourModel
{
    /**
     * @Column\Input(position="palette:my_palette")
     */
    protected string $description = '';

    /**
     * @Column\Input(position="tab:my_tab")
     */
    protected string $note = '';

    ...
}
```
It's possible to use the `tab:`-prefix for a palette's position, too.<br>
If a property references a field inside a palette there are two more special prefixes that can be used:
- newLineAfter
- newLineBefore

These will insert an additional `--linebreak--` between those fields.

The label of tabs and palettes is determined in the following way:
1. Use the annotation attribute if given. (can be a LLL-reference)
2. Use the default language label if existent.
   (see [Default language label paths and additional configuration options](#default-language-label-paths-and-additional-configuration-options))
3. Use the identifier.

The behaviour for the description of palettes is similar:
1. Use the annotation attribute if given. (can be a LLL-reference)
2. Use the default language label if existent.
   (see [Default language label paths and additional configuration options](#default-language-label-paths-and-additional-configuration-options))
3. Leave empty.

#### Extending domain models
When you are extending domain models (even from extensions that don't make use of psb_foundation) you have to add the
@TCA\Ctrl-annotation. You have the possibility to override ctrl-settings. If you don't want to override anything: just
leave out the brackets. The default values of the annotation class will have no effect in this case.

#### Default language label paths
These language labels will be tried if you don't provide a custom value for them.
The path always is `EXT:your_extension/Resources/Private/Language/Backend/Configuration/TCA/(Overrides/)[modelName].xlf`.

| Configuration value  | Default language label           |
|----------------------|----------------------------------|
| ctrl->title          | ctrl.title                       |
| property->label      | [propertyName]                   |
| palette->description | palette.[identifier].description |
| palette->label       | palette.[identifier].label       |
| tab->label           | tab.[identifier].label           |

When you use the items-property for a select field, you may provide a simple associative array.
It will be transformed into the required multi-level format.
The labels will be build this way:<br>
`[propertyName].[arrayKeyTransformedToLowerCamelCase]`

If you provide the file `EXT:your_extension_name/Resources/Private/Language/Backend/CSH/[tabelName].xlf`
it will be registered automatically.

#### Additional configuration options
A special property has been added to the Ctrl annotation:

| Property                  | Description                                                                                               |
|---------------------------|-----------------------------------------------------------------------------------------------------------|
| allowTableOnStandardPages | If set to true, you can insert records of a table on standard content pages (instead of SysFolders only). |

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
          \Your\Extension\Controller\YourController::class,
          \Your\Extension\Controller\AnotherController::class => [
              'actionName1', // This is not necessarily the default action!
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
  class YourController extends ActionController
  {
      /**
       * @PluginAction
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
even if mentioned in the optional action list in `ExtensionInformation.php`!<br>
If no action list is provided, all actions annotated with `PluginAction` will be registered.
Which action will be used as default action and which actions should not be cached is determined by the annotation values only.
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

- `EXT:your_extension/Resources/Private/Language/Backend/Configuration/TsConfig/Page/Mod/Wizards/newContentElement.xlf:`
    - `[group].elements.[pluginName].description`
    - `[group].elements.[pluginName].title`

If the label for title does not exist, this label will be tried:
`EXT:your_extension/Resources/Private/Language/Backend/Configuration/TCA/Overrides/tt_content.xlf:plugin.[pluginName].title`<br>
This label is also used for the select box item.
If it doesn't exist either, the plugin name will be used as fallback.

`[group]` defaults to the vendor name (lowercase) if not set within `PluginConfig`-annotation. That also defines the tab
of the content element wizard. If a new tab is created, its label will be fetched from
here: `EXT:your_extension/Resources/Private/Language/Backend/Configuration/TsConfig/Page/Mod/Wizards/newContentElement.xlf:[group].header`

#### Custom page types for single actions
```php
use PSB\PsbFoundation\Annotation\PageType;
use PSB\PsbFoundation\Annotation\PluginAction;

...

/**
 * @PluginAction(uncached=true)
 * @PageType(contentType=PageObjectConfiguration::CONTENT_TYPE_HTML, disableAllHeaderCode=false, typeNum=1589385441)
 */
public function specialPageTypeAction()
{
}
```

psb_foundation will create the necessary TypoScript so that this action can be called directly with the request parameter `type=1589385441`.

### Registering and configuring modules
This process is very similar to the way plugins are registered.

- Classes/Data/ExtensionInformation.php
  ```php
  public const MAIN_MODULES = [
      'mainModuleName',
      'mainModuleWithCustomConfiguration' => [
          'iconIdentifier' => '...', // optional
          'labels'         => '...', // optional
          'position'       => '...', // optional
          'routeTarget'    => '...', // optional
      ],
      ...
  ];

  public const MODULES = [
      'ModuleName' => [
          \Your\Extension\Controller\YourModuleController::class,
          \Your\Extension\Controller\AnotherModuleController::class,
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

Instead of `ActionController` you can extend `PSB\PsbFoundation\Controller\Backend\AbstractModuleController`.
This class provides the required `ModuleTemplateFactory`.
You can access the ModuleTemplate via `$this->moduleTemplate`, e.g. to add FlashMessages.
At the end of your actions you can just call `$this->render()` and your corresponding fluid-template will be returned.

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

Modules need to provide three labels:

| Label                 | Description                                                  |
|-----------------------|--------------------------------------------------------------|
| mlang_labels_tabdescr | used as module description in the about-module               |
| mlang_labels_tablabel | used as short description when hovering over the module link |
| mlang_tabs_tab        | used as module title                                         |

The following fallbacks account for main modules and submodules, if no custom value is specified:
- language file: `EXT:your_extension/Resources/Private/Language/Backend/Modules/[moduleName].xlf:` (file name starts with lower case!)
- icon identifier: `extension-key-module-your-module-name` (i.e. your filename would be `module-your-module-name`!)

Fallbacks regarding submodules only:
- access: `group, user`
- main module: `web`

Check the other configuration options and comments in `EXT:psb_foundation/Classes/Annotation/`.

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

### Auto-registration of TSconfig-files
If these files exist, they will be included automatically:
- `EXT:your_extension/Configuration/TSconfig/Page.tsconfig`
- `EXT:your_extension/Configuration/TSconfig/User.tsconfig`

### Auto-registration of icons
All PNG- and SVG-files located in `EXT:your_extension/Resources/Public/Icons` will be registered automatically.
The extension key and the file name are used as icon identifier.

Examples (filename => icon identifier):
- `logo.svg` => `your-extension-key-logo`
- `MainMenu.png` => `your-extension-key-main-menu`

### Extension settings
#### Log missing language labels
If activated, missing language labels will be stored in `tx_psbfoundation_missing_language_labels`. This is restricted
to `PSB\PsbFoundation\Service\LocalizationService` which extends the LocalizationService of the TYPO3 core. All missing
default labels (e.g. plugin title or field label) will be listed this way if you didn't provide a custom label. Fixed
entries get removed on next check (every time the cache is cleared).<br>
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
- convertString: performs a type cast or other operations based on the string's content, e.g.<br>

  | input value                                       | return type                                                                                 |
  |---------------------------------------------------|---------------------------------------------------------------------------------------------|
  | (empty string)                                    | empty string will be returned as empty string or null (depends on second argument)          |
  | `0`, `123`                                        | returns integer                                                                             |
  | `0.1`, `0,1`                                      | returns float                                                                               |
  | `0001423`                                         | returns unchanged string                                                                    |
  | `TS:config.headerComment`                         | returns value from TypoScript (if path is valid) which is also processed by this function.  |
  | `\Full\Qualified\ClassName::CONSTANT['arrayKey']` | returns value of constant which is also processed by this function.                         |
  | `{...}`, `[...]`                                  | returns array if valid JSON                                                                 |
  | `false`, `true`                                   | returns boolean                                                                             |

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
Not set constants will be returned as `null` instead of `{$...}`.
