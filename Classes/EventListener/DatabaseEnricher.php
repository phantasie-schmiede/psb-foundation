<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\EventListener;

use PSB\PsbFoundation\Attribute\TCA\Column;
use PSB\PsbFoundation\Utility\ArrayUtility;
use PSB\PsbFoundation\Utility\VariableUtility;
use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;

/**
 * Class DatabaseEnricher
 *
 * @package PSB\PsbFoundation\EventListener
 */
class DatabaseEnricher
{
    /**
     * @param AlterTableDefinitionStatementsEvent $event
     *
     * @return void
     */
    public function __invoke(AlterTableDefinitionStatementsEvent $event): void
    {
        $tca = $GLOBALS['TCA'];
        $configurationPaths = ArrayUtility::inArrayRecursive($tca, Column::DATABASE_DEFINITION_KEY);

        if (empty($configurationPaths)) {
            return;
        }

        $additionalFields = [];
        $sql = implode(LF, $event->getSqlData());

        foreach ($configurationPaths as $configurationPath) {
            $configurationPathParts = explode('.', $configurationPath);

            /*
             * Get this part:                          ⬇
             * $GLOBALS['TCA'][<tableName>]['columns'][X]['config']['databaseDefinition']
             */
            $fieldName = $configurationPathParts[count($configurationPathParts) - 3];

            /*
             * Get this part:  ⬇
             * $GLOBALS['TCA'][X]['columns'][<columnName>]['config']['databaseDefinition']
             */
            $tableName = $configurationPathParts[count($configurationPathParts) - 5];

            if (!$this->sqlHasFieldDefinition($fieldName, $sql, $tableName)) {
                $additionalFields[$tableName][$fieldName] = VariableUtility::getValueByPath($tca, $configurationPath);
            }
        }

        if (empty($additionalFields)) {
            return;
        }

        foreach ($additionalFields as $tableName => $fields) {
            $createStatement = 'CREATE TABLE ' . $tableName . ' (';

            foreach ($fields as $fieldName => $databaseDefinition) {
                $createStatement .= LF . '    ' . $fieldName . ' ' . $databaseDefinition . ',';
            }

            $createStatement .= rtrim($createStatement, ',') . LF . ');';

            $event->addSqlData($createStatement);
        }
    }

    /**
     * @param string $fieldName
     * @param string $sql
     * @param string $tableName
     *
     * @return bool
     */
    private function sqlHasFieldDefinition(string $fieldName, string $sql, string $tableName): bool
    {
        // @TODO: Scan raw SQL!
        return true;
    }
}
