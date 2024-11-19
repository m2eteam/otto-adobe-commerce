<?php

declare(strict_types=1);

namespace M2E\Otto\Model\HealthStatus\Task\Database\MysqlInfo;

use M2E\Otto\Model\HealthStatus\Task\IssueType;
use M2E\Otto\Model\HealthStatus\Task\Result as TaskResult;
use M2E\Otto\Model\Otto\Connector\System\Tables\GetDiffCommand;

class TablesStructure extends IssueType
{
    private \M2E\Otto\Model\HealthStatus\Task\Result\Factory $resultFactory;
    private \M2E\Otto\Model\Connector\Client\Single $serverClient;
    private \M2E\Otto\Helper\Module\Database\Structure $databaseHelper;

    public function __construct(
        \M2E\Otto\Model\Connector\Client\Single $serverClient,
        \M2E\Otto\Model\HealthStatus\Task\Result\Factory $resultFactory,
        \M2E\Otto\Helper\Module\Database\Structure $databaseHelper
    ) {
        parent::__construct();
        $this->databaseHelper = $databaseHelper;
        $this->serverClient = $serverClient;
        $this->resultFactory = $resultFactory;
    }

    public function process()
    {
        $tablesInfo = \M2E\Otto\Helper\Json::encode($this->databaseHelper->getModuleTablesInfo());

        $command = new GetDiffCommand($tablesInfo);
        /** @var \M2E\Otto\Model\Connector\Response $response */
        $response = $this->serverClient->process($command);
        $responseData = $response->getResponseData();

        $result = $this->resultFactory->create($this);
        $result->setTaskResult(TaskResult::STATE_SUCCESS);

        if (!isset($responseData['diff']) || count($responseData['diff']) <= 0) {
            return $result;
        }

        foreach ($responseData['diff'] as $tableName => $checkingResults) {
            foreach ($checkingResults as $resultRow) {
                $this->applyDiffResult($result, $resultRow);
            }
        }

        return $result;
    }

    private function applyDiffResult(TaskResult $taskResult, $diffResult)
    {
        if (
            $taskResult->getTaskResult() < TaskResult::STATE_CRITICAL
            && $diffResult['severity'] == GetDiffCommand::SEVERITY_CRITICAL
        ) {
            $taskResult->setTaskResult(TaskResult::STATE_CRITICAL);
            $taskResult->setTaskMessage(
                __(
                    'Some MySQL tables or their columns are missing. It can cause critical issues in Module work. '
                    . 'Please contact Support at <a href="mailto:support@m2epro.com">support@m2epro.com</a> for a solution.'
                )
            );

            return;
        }

        if (
            $taskResult->getTaskResult() < TaskResult::STATE_WARNING
            && $diffResult['severity'] == GetDiffCommand::SEVERITY_WARNING
        ) {
            $taskResult->setTaskResult(TaskResult::STATE_WARNING);
            $taskResult->setTaskMessage(
                __(
                    'Some MySQL tables or their columns may have incorrect definitions. '
                    . 'If you face any unusual behavior of the Module, please contact Support at '
                    . '<a href="mailto:support@m2epro.com">support@m2epro.com</a>.'
                )
            );

            return;
        }
    }
}