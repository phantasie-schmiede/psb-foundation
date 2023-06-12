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
use PSB\PsbFoundation\Utility\StringUtility;
use PSB\PsbFoundation\Utility\VariableUtility;
use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;

/**
 * Class DatabaseEnricher
 *
 * @package PSB\PsbFoundation\EventListener
 */
class DatabaseEnricher
{
    protected const CREATE_TABLE_PHRASE = 'CREATE TABLE';
    protected const SKIP_KEYWORDS       = [
        'KEY',
        'PRIMARY',
        'UNIQUE',
    ];

    protected array $originalFields = [];

    /**
     * @param AlterTableDefinitionStatementsEvent $event
     *
     * @return void
     */
    public function __invoke(AlterTableDefinitionStatementsEvent $event): void
    {
        $tca = $GLOBALS['TCA'];
        $configurationPaths = ArrayUtility::inArrayRecursive($tca, Column::DATABASE_DEFINITION_KEY, true);

        if (empty($configurationPaths)) {
            return;
        }

        $additionalFields = [];
        $sql = implode(LF, $event->getSqlData());
        $this->originalFields = $this->generateDataFromRawSql($sql);

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

            if (!$this->sqlHasFieldDefinition($fieldName, $tableName)) {
                $additionalFields[$tableName][$fieldName] = VariableUtility::getValueByPath($tca, $configurationPath);
            }
        }

        if (empty($additionalFields)) {
            return;
        }

        foreach ($additionalFields as $tableName => $fields) {
            $createStatement = self::CREATE_TABLE_PHRASE . ' ' . $tableName . ' (';

            foreach ($fields as $fieldName => $databaseDefinition) {
                $createStatement .= LF . '    ' . $fieldName . ' ' . $databaseDefinition . ',';
            }

            $createStatement = rtrim($createStatement, ',') . LF . ');';
            $event->addSqlData($createStatement);
        }
    }

    /**
     * @param string $sql
     *
     * @return array
     */
    private function generateDataFromRawSql(string $sql): array
    {
        $data = [];

        foreach (StringUtility::explodeByLineBreaks($sql) as $line) {
            $line = trim($line);

            // Skip empty lines and comments.
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            // Skip certain lines like defintion of keys.
            foreach (self::SKIP_KEYWORDS as $skipKeyword) {
                if (str_starts_with($line, $skipKeyword . ' ')) {
                    continue 2;
                }
            }

            // Detect end of table definition.
            if (str_contains($line, ';')) {
                $tableName = null;
                continue;
            }

            if (str_starts_with($line, self::CREATE_TABLE_PHRASE)) {
                // Remove 'CREATE TABLE'.
                $line = trim(substr($line, strlen(self::CREATE_TABLE_PHRASE)));
                $tableName = StringUtility::getFirstWord($line);
                continue;
            }

            if (!empty($tableName)) {
                /*
                 * Should be a field definition at this point!
                 * Remove backticks.
                 */
                $line = trim($line, '`');
                $fieldName = StringUtility::getFirstWord($line);
                $data[$tableName][] = $fieldName;
            }
        }

        return $data;
    }

    /**
     * @param string $fieldName
     * @param string $tableName
     *
     * @return bool
     */
    private function sqlHasFieldDefinition(string $fieldName, string $tableName): bool
    {
        return in_array($fieldName, $this->originalFields[$tableName] ?? [], true);
    }
}
