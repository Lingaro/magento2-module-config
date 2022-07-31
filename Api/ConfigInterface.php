<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

declare(strict_types=1);

namespace Orba\Config\Api;

interface ConfigInterface
{
    public function getConfigId(): ?string;
    public function getPath(): string;
    public function getValue(): ?string;
    public function getScopeType(): string;
    public function getScopeCode(): ?string;
    public function getScopeId(): ?int;
    public function getimportedValueHash() : string;
    public function getAllData() : array;
}
