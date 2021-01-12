# PSB Foundation
## Enhanced extension programming in Extbase

- [Get started](#get-started)

### Proclaimer
The concept of this extension is based in most parts on the developer following the conventions and rules defined by Extbase, unless this documentation describes otherwise.

### Getting started
Install psb_foundation and add it to the dependencies of your extension.
Create the following file: `your_extension_directory/Classes/Data/ExtensionInformation.php`.
Create a class and make it extend `PSB\PsbFoundation\Data\AbstractExtensionInformation`.

Example:
```php
<?php
declare(strict_types=1);
namespace Vendor\ExtensionName\Data;

/** copyright... */

use PSB\PsbFoundation\Data\AbstractExtensionInformation;

class ExtensionInformation extends AbstractExtensionInformation
{
}
```
When you install your extension, psb_foundation will look for that file and if it exists, it is registered in the table `tx_psbfoundation_extension_information_mapping`.
This table is used for a variety of operations that you won't have to take care of anymore.
You can use inherited functions like `getExtensionKey()` or `getVendorName()` to get rid of hard-coded identifiers.
Renaming your extension during development will require less effort.

### Registering and configuring plugins
#### Old way
- ext_localconf.php
  ```php
  \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
      'extension_key',
      'pluginName',
      [
          \Your\Extension\Controller\YourController::class => 'firstAction, secondAction, thirdAction',
      ],
      [
          \Your\Extension\Controller\YourController::class => 'thirdAction',
      ]
  );
  ```

- Configuration/Tca/Overrides/tt_content.php
  ```php
  \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
      'extension_key',
      'pluginName',
      'backend title'
  );
  ```

- Configuration/TsConfig/Page/Mod/Wizards/NewContentElement.tsconfig
  ```
  mod.wizards {
      newContentElement.wizardItems {
          plugins {
              elements {
                  extensionkey_pluginname {
                      iconIdentifier = 'ext-icon'
                      title = 'backend title'
                      description = 'plugin description'
                      tt_content_defValues {
                          CType = list
                          list_type = extensionkey_pluginname
                      }
                  }
              }
          }
      }
  }
  ```

#### New way
- Classes/Data/ExtensionInformation.php
  ```php
  public const PLUGINS = [
      'pluginName' => [
          \Your\Extension\Controller\YourController::class,
      ],
  ];
  ```

- Classes/Controller/YourController.php
  ```php
  use PSB\PsbFoundation\Service\DocComment\Annotations\PluginAction;
  use PSB\PsbFoundation\Service\DocComment\Annotations\PluginConfig;

  /**
   * @PluginConfig(iconIdentifier="special-icon")
   */
  class FirstController extends ActionController
  {
      /**
       * @PluginAction(default=true)
       */
      public function firstAction(): void
      {
      }

      public function secondAction(): void
      {
      }

      /**
       * @PluginAction (uncached=true)
       */
      public function thirdAction(): void
      {
      }
  }
  ```

If you follow certain conventions, you may omit certain annotations.
The following language labels are taken into account automatically if defined:
- \[extension_key\]/Resources/Private/Language/Backend/Configuration/TSConfig/Page/wizard.xlf:
  * \[group\].elements.\[pluginName\].description
  * \[group\].elements.\[pluginName\].title

\[group\] defaults to the vendor name (lowercase) if not defined with @PluginConfig.
That also defines the tab of the content element wizard.
If a new tab is created, its label will be fetched from here:
- \[extension_key\]/Resources/Private/Language/Backend/Configuration/TSConfig/Page/wizard.xlf:
  * \[group\].header

### TCA-generation
For typical use-cases you don't have to define TCA-files anymore.
psb_foundation will scan your `Classes/`-directory for classes lying in the `Domain\Model`-namespace skipping abstract classes and interfaces.
The script checks if your model relates to an existing table in the database and detects if it extends another model from a different extension and manipulates the TCA accordingly.

You can provide configuration options via PHPDoc-annotations.
Available annotations can be found in `psb_foundation/Classes/Service/DocComment/Annotations/`.

Example:
```php
use PSB\PsbFoundation\Service\DocComment\Annotations\TCA;

/**
 * @TCA\Ctrl (label=name, searchFields="description, name")
 */
class YourClass
{
    /**
     * @var string
     * @TCA\Input (eval="trim,required")
     */
    protected string $name;

    /**
     * @var bool
     * @TCA\Checkbox (exclude=1)
     */
    protected bool $restricted;

    ...
}
```

Example:
```php
use PSB\PsbFoundation\Service\DocComment\Annotations\PropertyMapping;
use PSB\PsbFoundation\Service\DocComment\Annotations\TCA;

class YourClass
{
    /**
     * @var string
     * @TCA\Input
     * @PropertyMapping(column=your_extensionkey_nickname)
     */
    protected string $nickname;

    ...
}
```

Each `type` has its default configuration, and you only need to specify values that differ from it.

#### List of available types for @TcaFieldConfig and their default configuration
As defined in `psb_foundation/Classes/Service/Configuration/Fields.php`:

- checkbox
  ```
  'default' => 0,
  'type'    => 'check',

- date
  ```
  'dbType'     => 'date',
  'default'    => '0000-00-00',
  'eval'       => 'date',
  'renderType' => 'inputDateTime',
  'size'       => 7,
  'type'       => 'input',

- datetime
  ```
  'eval'       => 'datetime',
  'renderType' => 'inputDateTime',
  'size'       => 12,
  'type'       => 'input',

- document
  ```

- file
  ```

- float
  ```
  'eval' => 'double2',
  'size' => 20,
  'type' => 'input',

- group
  ```
  'allowed'       => 'pages',
  'internal_type' => 'db',
  'maxitems'      => 1,
  'minitems'      => 0,
  'size'          => 3,
  'type'          => 'group',

- image
  ```

- inline
  ```
  'appearance'    => [
      'collapseAll'                     => true,
      'enabledControls'                 => [
          'dragdrop' => true,
      ],
      'expandSingle'                    => true,
      'levelLinksPosition'              => 'bottom',
      'showAllLocalizationLink'         => true,
      'showPossibleLocalizationRecords' => true,
      'showRemovedLocalizationRecords'  => true,
      'showSynchronizationLink'         => true,
      'useSortable'                     => true,
  ],
  'foreign_field' => '',
  'foreign_table' => '',
  'maxitems'      => 9999,
  'type'          => 'inline',

- integer
  ```
  'eval' => 'num',
  'size' => 20,
  'type' => 'input',

- link
  ```
  'renderType' => 'inputLink',
  'size'       => 20,
  'type'       => 'input',

- mm
  ```
  'autoSizeMax'   => 30,
  'foreign_table' => '',
  'maxitems'      => 9999,
  'mm'            => '',
  'multiple'      => 0,
  'renderType'    => 'selectMultipleSideBySide',
  'size'          => 10,
  'type'          => 'select',

- passhtrough
  ```
  'type' => 'passthrough',

- select
  ```
  'maxitems'   => 1,
  'renderType' => 'selectSingle',
  'type'       => 'select',

- string
  ```
  'eval' => 'trim',
  'size' => 20,
  'type' => 'input',

- text
  ```
  'cols'           => 32,
  'enableRichtext' => true,
  'eval'           => 'trim',
  'rows'           => 5,
  'type'           => 'text',

- user
  ```
  'eval'       => 'required,trim',
  'parameters' => [],
  'size'       => 50,
  'type'       => 'user',
  'userFunc'   => '',
