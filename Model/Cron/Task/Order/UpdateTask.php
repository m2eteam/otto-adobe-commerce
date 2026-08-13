<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Cron\Task\Order;

class UpdateTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'order/update';

    private \M2E\Otto\Model\Account\Repository $accountRepository;
    private \M2E\Otto\Model\Order\Change\Repository $changeRepository;
    private \M2E\Otto\Model\Order\Change\ShipmentProcessor $shipmentProcessor;
    private \M2E\Otto\Model\Order\Change\DeleteService $deleteService;

    public function __construct(
        \M2E\Otto\Model\Order\Change\ShipmentProcessor $shipmentProcessor,
        \M2E\Otto\Model\Account\Repository $accountRepository,
        \M2E\Otto\Model\Order\Change\Repository $changeRepository,
        \M2E\Otto\Model\Order\Change\DeleteService $deleteService
    ) {
        $this->accountRepository = $accountRepository;
        $this->changeRepository = $changeRepository;
        $this->shipmentProcessor = $shipmentProcessor;
        $this->deleteService = $deleteService;
    }

    /**
     * @param \M2E\Otto\Model\Cron\TaskContext $context
     *
     * @return void
     */
    public function process($context): void
    {
        $context->getSynchronizationLog()->setTask(\M2E\Otto\Model\Synchronization\Log::TASK_ORDERS);

        $this->deleteNotActualChanges();

        foreach ($this->accountRepository->getAll() as $account) {
            $context->getOperationHistory()->addText('Starting Account "' . $account->getTitle() . '"');

            try {
                $this->shipmentProcessor->process($account);
            } catch (\Throwable $exception) {
                $message = (string)__(
                    'The "Update" Action for Account "%1" was completed with error.',
                    $account->getTitle()
                );

                $context->getExceptionHandler()->processTaskAccountException($message, __FILE__, __LINE__);
                $context->getExceptionHandler()->processTaskException($exception);
            }
        }
    }

    private function deleteNotActualChanges(): void
    {
        $orderChanges = $this->changeRepository->getItemsExceededProcessingAttemptCount(
            \M2E\Otto\Model\Order\Change::MAX_ALLOWED_PROCESSING_ATTEMPTS,
            \M2E\Otto\Model\Order\Change::ACTION_UPDATE_SHIPPING
        );

        foreach ($orderChanges as $change) {
            $this->deleteService->deleteChangeWithUpdateShippingAction($change);
        }
    }
}
