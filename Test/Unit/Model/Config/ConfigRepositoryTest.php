<?php

namespace Orba\Config\Test\Unit\Model\Csv;

use Magento\Framework\TestFramework\Unit\BaseTestCase;
use Orba\Config\Model\Config\ConfigRepository;
use PHPUnit\Framework\MockObject\MockObject;

class ConfigRepositoryTest extends BaseTestCase
{
    /** @var MockObject[] */
    private $arguments;

    /** @var ConfigRepository */
    private $repository;

    protected function setUp()
    {
        parent::setUp();
        $this->arguments = $this->objectManager->getConstructArguments(ConfigRepository::class);
        $this->repository = $this->objectManager->getObject(ConfigRepository::class, $this->arguments);
    }
}
