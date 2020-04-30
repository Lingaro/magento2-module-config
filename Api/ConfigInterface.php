<?php

namespace Orba\Config\Api;

interface ConfigInterface
{
    public function getPath(): string;
    public function getValue(): string;
    public function getScopeType(): string;
    public function getScopeCode(): ?string;
}
