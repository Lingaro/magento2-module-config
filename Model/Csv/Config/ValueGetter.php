<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Model\Csv\Config;

use Magento\Config\Model\Config\StructureFactory;
use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Config\Model\Config\StructureElementInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Config\Model\Config\BackendFactory;

class ValueGetter
{
    public const MODEL_PARSE_METHOD = 'beforeSave';

    /**
     * @var StructureFactory
     */
    private StructureFactory $structureFactory;

    /**
     * @var array
     */
    private array $backendModels = [];

    /**
     * @var BackendFactory
     */
    private BackendFactory $valueFactory;

    public function __construct(
        StructureFactory $structureFactory,
        BackendFactory $valueFactory
    ) {
        $this->structureFactory = $structureFactory;
        $this->valueFactory = $valueFactory;
    }

    /**
     * @param string $path
     * @return string|null
     */
    private function getBackendModelClass(string $path): ?string
    {
        $structure = $this->structureFactory->create();

        /** @var StructureElementInterface $field */
        $field = $structure->getElementByConfigPath($path);

        return $field instanceof Field && $field->hasBackendModel()
            ? $field->getData()['backend_model']
            : null;
    }

    /**
     * @param string $path
     * @return AbstractModel|null
     */
    private function getBackendModel(string $path): ?AbstractModel
    {
        $backendModelName = $this->getBackendModelClass($path);
        if (!$backendModelName) {
            return null;
        }

        if (!array_key_exists($backendModelName, $this->backendModels)) {
            $backendModel = $this->valueFactory->create(
                $backendModelName,
                []
            );

            if (!($backendModel instanceof AbstractModel ||
                ($backendModel instanceof DataObject && method_exists($backendModel, self::MODEL_PARSE_METHOD))
            )) {
                $backendModel = null;
            }

            $this->backendModels[$backendModelName] = $backendModel;
        }

        return $this->backendModels[$backendModelName];
    }

    public function getValueWithBackendModel(string $path, ?string $value): ?string
    {
        $backendModel = $this->getBackendModel($path);
        if ($backendModel === null) {
            return $value;
        }

        $backendModel->setValue($value);
        $backendModel->setPath($path);
        $methodName = self::MODEL_PARSE_METHOD;
        $backendModel->$methodName();

        return (string)$backendModel->getValue();
    }
}
