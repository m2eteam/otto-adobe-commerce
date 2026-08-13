<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Cron\Task\Order;

class CancelTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'order/cancel';

    private \M2E\Otto\Model\Order\Change\CancelProcessor $cancelProcessor;
    private \M2E\Otto\Model\Account\Repository $accountRepository;
    private \M2E\Otto\Model\Order\Change\Repository $orderChangeRepository;
    private \M2E\Otto\Model\Order\Change\DeleteService $deleteService;

    public function __construct(
        \M2E\Otto\Model\Order\Change\CancelProcessor $cancelProcessor,
        \M2E\Otto\Model\Account\Repository $accountRepository,
        \M2E\Otto\Model\Order\Change\Repository $orderChangeRepository,
        \M2E\Otto\Model\Order\Change\DeleteService $deleteService
    ) {
        $this->cancelProcessor = $cancelProcessor;
        $this->accountRepository = $accountRepository;
        $this->orderChangeRepository = $orderChangeRepository;
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
            $this->cancelProcessor->process($account);
        }
    }

    private function deleteNotActualChanges(): void
    {
        $orderChanges = $this->orderChangeRepository->getItemsExceededProcessingAttemptCount(
            \M2E\Otto\Model\Order\Change::MAX_ALLOWED_PROCESSING_ATTEMPTS,
            \M2E\Otto\Model\Order\Change::ACTION_CANCEL
        );

        foreach ($orderChanges as $orderChange) {
            $this->deleteService->deleteChangeWithCancelAction($orderChange);
        }
    }
}
