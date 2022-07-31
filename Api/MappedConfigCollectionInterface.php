<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

declare(strict_types=1);

namespace Orba\Config\Api;

use ArrayAccess;
use Countable;
use Iterator;

interface MappedConfigCollectionInterface extends ArrayAccess, Countable, Iterator
{
    public function mergeOtherCollections(MappedConfigCollectionInterface ...$otherCollections): MappedConfigCollectionInterface;
    public function getOriginalData(): array;
    public function has(ConfigInterface $config): bool;
    public function getFromCollection(ConfigInterface $config): ?ConfigInterface;
    public function add(ConfigInterface $config): MappedConfigCollectionInterface;
    public function remove(ConfigInterface $config): MappedConfigCollectionInterface;
}
