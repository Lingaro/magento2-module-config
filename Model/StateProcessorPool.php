<?php
namespace Orba\Config\Model;

use Orba\Config\Model\StateProcessorInterface;
use Exception;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class StateProcessorPool
 */
class StateProcessorPool
{
    public const CONFIG_FIELD_CLASS = 'class';
    public const CONFIG_FIELD_DISABLE = 'disable';

    /** @var ObjectManagerInterface */
    protected $objectManager;

    /** @var StateProcessorInterface[] $processors */
    protected $processors = [];

    /**
     * StateProcessorPool constructor.
     * @param ObjectManagerInterface $objectManager
     * @param array $processors
     * @throws Exception
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $processors
    ) {
        $this->objectManager = $objectManager;
        $this->processors = $this->prepareProcessors($processors);
    }

    /**
     * @param array $processors
     * @return array
     * @throws Exception
     */
    protected function prepareProcessors(array $processors) : array
    {
        $result = [];

        foreach ($processors as $processorCode => $processorConfig) {
            if ($processorConfig[self::CONFIG_FIELD_DISABLE]) {
                continue;
            }
            $class = $processorConfig[self::CONFIG_FIELD_CLASS];
            if (!is_subclass_of($class, StateProcessorInterface::class)) {
                throw new Exception(sprintf(
                    'StateProcessor %s does not implement %s',
                    $class,
                    StateProcessorInterface::class
                ));
            }

            $result[$processorCode] = $this->objectManager->get($class);
        }

        return $result;
    }

    /**
     * @param string $code
     * @return StateProcessorInterface
     * @throws Exception
     */
    public function get(string $code) : StateProcessorInterface
    {
        if (empty($this->processors[$code])) {
            throw new Exception(sprintf(
                'No such StateProcessor: %s',
                $code
            ));
        }

        return $this->processors[$code];
    }
}
