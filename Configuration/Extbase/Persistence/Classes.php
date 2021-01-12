<?php
declare(strict_types=1);

return call_user_func(
    static function () {
        $allExtensionInformation = \PSB\PsbFoundation\Utility\ExtensionInformationUtility::getExtensionInformation();
        $mappingInformation = [];

        foreach ($allExtensionInformation as $extensionInformation) {
            $docCommentParserService = \PSB\PsbFoundation\Utility\ObjectUtility::get(\PSB\PsbFoundation\Service\DocComment\DocCommentParserService::class);
            $extensionMappings = $extensionInformation->getMapping();

            foreach ($extensionMappings as $className => $tableName) {
                $domainModel = \PSB\PsbFoundation\Utility\ObjectUtility::get(ReflectionClass::class, $className);
                $properties = $domainModel->getProperties();
                $propertyMappings = [];

                foreach ($properties as $property) {
                    $propertyName = $property->getName();
                    $docComment = $docCommentParserService->parsePhpDocComment($className,
                        $propertyName);

                    if (isset($docComment[\PSB\PsbFoundation\Service\DocComment\Annotations\PropertyMapping::class])) {
                        /** @var \PSB\PsbFoundation\Service\DocComment\Annotations\PropertyMapping $propertyMapping */
                        $propertyMapping = $docComment[\PSB\PsbFoundation\Service\DocComment\Annotations\PropertyMapping::class];
                        $propertyMappings[$propertyName] = ['fieldName' => $propertyMapping->getColumn()];
                    }
                }

                $mappingInformation[$className] = [
                    'properties' => $propertyMappings,
                    'tableName'  => $tableName,
                ];
            }
        }

        return $mappingInformation;
    }
);
