<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order\Change;

class CancelProcessor
{
    private const MAX_CHANGE_FOR_PROCESS = 50;

    /** @var \M2E\Otto\Model\Order\Change\Repository */
    private \M2E\Otto\Model\Order\Change\Repository $changeRepository;
    private \M2E\Otto\Model\Order\Repository $orderRepository;
    private \M2E\Otto\Model\Otto\Connector\Order\Cancel\Entity\Processor $connectorProcessor;
    private \M2E\Otto\Model\Order\Change\DeleteService $deleteService;

    public function __construct(
        \M2E\Otto\Model\Order\Change\Repository $changeRepository,
        \M2E\Otto\Model\Order\Repository $orderRepository,
        \M2E\Otto\Model\Otto\Connector\Order\Cancel\Entity\Processor $connectorProcessor,
        \M2E\Otto\Model\Order\Change\DeleteService $deleteService
    ) {
        $this->changeRepository = $changeRepository;
        $this->orderRepository = $orderRepository;
        $this->connectorProcessor = $connectorProcessor;
        $this->deleteService = $deleteService;
    }

    public function process(\M2E\Otto\Model\Account $account): void
    {
        $changes = $this->changeRepository
            ->findCanceledReadyForProcess($account, self::MAX_CHANGE_FOR_PROCESS);

        foreach ($changes as $change) {
            if (!$account->getOrdersSettings()->isCancelChannelOrderIfCreditmemoCreatedEnabled()) {
                $this->deleteService->deleteChangeWithCancelAction($change);

                continue;
            }

            $order = $this->orderRepository->find($change->getOrderId());
            if ($order === null) {
                $this->deleteService->deleteChangeWithCancelAction($change);

                continue;
            }

            if (!$order->canCancel()) {
                $this->deleteService->deleteChangeWithCancelAction($change);

                continue;
            }

            $changeParams = $change->getParams();
            if (
                empty($changeParams)
                || !isset($changeParams[\M2E\Otto\Model\Order\Change::PARAMS_KEY_ORDER_ITEMS])
                || empty($changeParams[\M2E\Otto\Model\Order\Change::PARAMS_KEY_ORDER_ITEMS])
            ) {
                $this->deleteService->deleteChangeWithCancelAction($change);

                continue;
            }

            $orderItemIds = [];
            foreach ($changeParams[\M2E\Otto\Model\Order\Change::PARAMS_KEY_ORDER_ITEMS] as $item) {
                $orderItem = $order->findItem((int)$item['id']);
                if ($orderItem === null) {
                    continue;
                }
                $orderItemIds[] = $orderItem->getOttoItemId();
            }

            if (empty($orderItemIds)) {
                $this->deleteService->deleteChangeWithCancelAction($change);

                continue;
            }

            $this->changeIncreaseAttempt($change);
            $response = $this->connectorProcessor
                ->process($account, $order->getOttoOrderId(), $orderItemIds);
            $this->deleteService->delete($change);

            $channelTitle = \M2E\Otto\Helper\Module::getChannelTitle();
            if (!$response->getMessageCollection()->hasErrors()) {
                $order->addSuccessLog(
                    'Order was cancelled on %channel_title%.',
                    ['channel_title' => $channelTitle]
                );

                continue;
            }

            foreach ($response->getMessageCollection()->getMessages() as $message) {
                if ($message->isError()) {
                    $order->addErrorLog(
                        'Order failed to be canceled on %channel_title%. Reason: %msg%',
                        [
                            'channel_title' => $channelTitle,
                            'msg' => $message->getText(),
                        ],
                    );
                } else {
                    $order->addWarningLog($message->getText());
                }
            }
        }
    }

    private function changeIncreaseAttempt(\M2E\Otto\Model\Order\Change $change): void
    {
        $change->increaseAttempt();

        $this->changeRepository->save($change);
    }
}
