<?php

namespace Orba\Config\Model;

use Magento\Framework\Exception\LocalizedException;
use Orba\Config\Api\ConfigInterface;
use Orba\Config\Api\MappedConfigCollectionInterface;
use Orba\Config\Helper\ConfigKeyGenerator;

class MappedConfigCollection implements MappedConfigCollectionInterface
{
    private $mappedData = [];

    private $keyGenerator;

    public function __construct(ConfigKeyGenerator $keyGenerator)
    {
        $this->keyGenerator = $keyGenerator;
    }

    public function current()
    {
        return current($this->mappedData);
    }

    public function next()
    {
        next($this->mappedData);
    }

    public function key()
    {
        return key($this->mappedData);
    }

    public function valid()
    {
        return key($this->mappedData) !== null;
    }

    public function rewind()
    {
        reset($this->mappedData);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->mappedData);
    }

    public function offsetGet($offset)
    {
        return $this->mappedData[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new LocalizedException(
            __('You can not set value in the collection directly')
        );
    }

    public function offsetUnset($offset)
    {
        throw new LocalizedException(
            __('You can not remove value in the collection directly')
        );

    }

    public function count()
    {
        return count($this->mappedData);
    }

    public function getOriginalData(): array
    {
        return $this->mappedData ?? [];
    }

    /**
     * @param MappedConfigCollectionInterface[] ...$otherCollections
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

    public function add(ConfigInterface $config): MappedConfigCollectionInterface
    {
        $key = $this->keyGenerator->generateKey($config);
        $this->mappedData[$key] = $config;
        return $this;
    }

    public function remove(ConfigInterface $config): MappedConfigCollectionInterface
    {
        $key = $this->keyGenerator->generateKey($config);
        if ($this->offsetExists($key)) {
            unset($this->mappedData[$key]);
        }
        return $this;
    }

    public function has(ConfigInterface $config): bool
    {
        $key = $this->keyGenerator->generateKey($config);
        return $this->offsetExists($key);
    }

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
