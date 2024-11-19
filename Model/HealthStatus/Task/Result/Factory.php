<?php

declare(strict_types=1);

namespace M2E\Otto\Model\HealthStatus\Task\Result;

use M2E\Otto\Model\HealthStatus\Task\Result as TaskResult;

class Factory
{
    /** @var \M2E\Otto\Model\HealthStatus\Task\Result\LocationResolver */
    protected $locationResolver;

    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $_objectManager = null;

    public function __construct(
        \M2E\Otto\Model\HealthStatus\Task\Result\LocationResolver $locationResolver,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->locationResolver = $locationResolver;
        $this->_objectManager = $objectManager;
    }

    /**
     * @param \M2E\Otto\Model\HealthStatus\Task\AbstractModel $task
     *
     * @return \M2E\Otto\Model\HealthStatus\Task\Result
     */
    public function create(\M2E\Otto\Model\HealthStatus\Task\AbstractModel $task): TaskResult
    {
        return $this->_objectManager->create(
            TaskResult::class,
            [
                'taskHash' => \M2E\Otto\Helper\Client::getClassName($task),
                'taskType' => $task->getType(),
                'taskMustBeShownIfSuccess' => $task->mustBeShownIfSuccess(),
                'tabName' => $this->locationResolver->resolveTabName($task),
                'fieldSetName' => $this->locationResolver->resolveFieldSetName($task),
                'fieldName' => $this->locationResolver->resolveFieldName($task),
            ],
        );
    }
}