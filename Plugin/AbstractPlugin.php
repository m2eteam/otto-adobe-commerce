<?php

declare(strict_types=1);

namespace M2E\Otto\Plugin;

use M2E\Otto\Model\Exception;

abstract class AbstractPlugin
{
    protected \M2E\Otto\Helper\Factory $helperFactory;

    public function __construct(
        \M2E\Otto\Helper\Factory $helperFactory
    ) {
        $this->helperFactory = $helperFactory;
    }

    /**
     * @throws \M2E\Otto\Model\Exception
     */
    protected function execute($name, $interceptor, \Closure $callback, array $arguments = [])
    {
        if (!$this->canExecute()) {
            return empty($arguments)
                ? $callback()
                : call_user_func_array($callback, $arguments);
        }

        $processMethod = 'process' . ucfirst($name);
        if (!method_exists($this, $processMethod)) {
            throw new Exception("Method $processMethod doesn't exists");
        }

        return $this->{$processMethod}($interceptor, $callback, $arguments);
    }

    protected function canExecute(): bool
    {
        /** @var \M2E\Otto\Helper\Magento $magentoHelper */
        $magentoHelper = $this->helperFactory->getObject('Magento');
        if ($magentoHelper->isInstalled() === false) {
            return false;
        }

        /** @var \M2E\Otto\Helper\Module\Maintenance $maintenanceHelper */
        $maintenanceHelper = $this->helperFactory->getObject('Module\Maintenance');
        if ($maintenanceHelper->isEnabled()) {
            return false;
        }

        /** @var \M2E\Otto\Helper\Module $moduleHelper */
        $moduleHelper = $this->helperFactory->getObject('Module');
        if (!$moduleHelper->isReadyToWork()) {
            return false;
        }

        if ($moduleHelper->isDisabled()) {
            return false;
        }

        return true;
    }

    /**
     * @param $helperName
     *
     * @return object
     */
    protected function getHelper($helperName): object
    {
        return $this->helperFactory->getObject($helperName);
    }
}
