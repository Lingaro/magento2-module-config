<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Model;

use Magento\Framework\Exception\LocalizedException;
use Lingaro\Config\Api\ConfigInterface;
use Lingaro\Config\Api\MappedConfigCollectionInterface;
use Lingaro\Config\Helper\ConfigKeyGenerator;

class MappedConfigCollection implements MappedConfigCollectionInterface
{
    /**
     * @var array
     */
    private array $mappedData = [];

    /**
     * @var ConfigKeyGenerator
     */
    private ConfigKeyGenerator $keyGenerator;

    /**
     * @param ConfigKeyGenerator $keyGenerator
     */
    public function __construct(ConfigKeyGenerator $keyGenerator)
    {
        $this->keyGenerator = $keyGenerator;
    }

    /**
     * @return false|mixed
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return current($this->mappedData);
    }

    /**
     * @return void
     */
    public function next(): void
    {
        next($this->mappedData);
    }

    /**
     * @return int|string|null
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return key($this->mappedData);
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return key($this->mappedData) !== null;
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        reset($this->mappedData);
    }

    /**
     * @param $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->mappedData);
    }

    /**
     * @param $offset
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->mappedData[$offset];
    }

    /**
     * @param $offset
     * @param $value
     * @return void
     * @throws LocalizedException
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        throw new LocalizedException(
            __('You can not set value in the collection directly')
        );
    }

    /**
     * @param $offset
     * @return void
     * @throws LocalizedException
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        throw new LocalizedException(
            __('You can not remove value in the collection directly')
        );
    }

    /**
     * @return int|void
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->mappedData);
    }

    /**
     * @return array
     */
    public function getOriginalData(): array
    {
        return $this->mappedData ?? [];
    }

    /**
     * @param MappedConfigCollectionInterface ...$otherCollections
     * @return MappedConfigCollectionInterface
     */
    public function mergeOtherCollections(MappedConfigCollectionInterface ...$otherCollections): MappedConfigCollectionInterface {
        $otherDatas = array_map(
            function (MappedConfigCollectionInterface $collection): array {
                return $collection->getOriginalData();
            },
            $otherCollections
        );
        $this->mappedData = array_merge(
            $this->mappedData,
            ...$otherDatas
        );

        return $this;
    }

    /**
     * @param ConfigInterface $config
     * @return MappedConfigCollectionInterface
     */
    public function add(ConfigInterface $config): MappedConfigCollectionInterface
    {
        $key = $this->keyGenerator->generateKey($config);
        $this->mappedData[$key] = $config;
        return $this;
    }

    /**
     * @param ConfigInterface $config
     * @return MappedConfigCollectionInterface
     */
    public function remove(ConfigInterface $config): MappedConfigCollectionInterface
    {
        $key = $this->keyGenerator->generateKey($config);
        if ($this->offsetExists($key)) {
            unset($this->mappedData[$key]);
        }
        return $this;
    }

    /**
     * @param ConfigInterface $config
     * @return bool
     */
    public function has(ConfigInterface $config): bool
    {
        $key = $this->keyGenerator->generateKey($config);
        return $this->offsetExists($key);
    }

    /**
     * @param ConfigInterface $config
     * @return ConfigInterface|null
     */
    public function getFromCollection(ConfigInterface $config): ?ConfigInterface
    {
        $key = $this->keyGenerator->generateKey($config);
        if ($this->offsetExists($key)) {
            return $this->offsetGet($key);
        } else {
            return null;
        }
    }
}
