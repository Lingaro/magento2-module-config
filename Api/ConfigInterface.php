<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Api;

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
