<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order\Change;

class DeleteService
{
    /** @var \M2E\Otto\Model\Order\Change\Repository */
    private Repository $orderChangeRepository;
    private \M2E\Otto\Model\Order\Repository $orderRepository;
    private \M2E\Otto\Model\Order\Item\Repository $orderItemRepository;

    public function __construct(
        \M2E\Otto\Model\Order\Change\Repository $orderChangeRepository,
        \M2E\Otto\Model\Order\Repository $orderRepository,
        \M2E\Otto\Model\Order\Item\Repository $orderItemRepository
    ) {
        $this->orderChangeRepository = $orderChangeRepository;
        $this->orderRepository = $orderRepository;
        $this->orderItemRepository = $orderItemRepository;
    }

    public function deleteChangeWithCancelAction(\M2E\Otto\Model\Order\Change $change): void
    {
        if (!$change->isCancelAction()) {
            return;
        }

        $order = $this->orderRepository->find($change->getOrderId());
        if ($order !== null) {
            $changeParams = $change->getParams();
            $orderItems = $changeParams[\M2E\Otto\Model\Order\Change::PARAMS_KEY_ORDER_ITEMS] ?? [];
            foreach ($orderItems as $item) {
                $orderItem = $order->findItem((int)$item['id']);
                if ($orderItem === null) {
                    continue;
                }

                $orderItem->setIsCancellationInProgressNo();
                $this->orderItemRepository->save($orderItem);
            }
        }

        $this->delete($change);
    }

    public function deleteChangeWithUpdateShippingAction(\M2E\Otto\Model\Order\Change $change): void
    {
        if (!$change->isShippingUpdateAction()) {
            return;
        }

        $this->delete($change);
    }

    public function delete(\M2E\Otto\Model\Order\Change $change): void
    {
        $this->orderChangeRepository->delete($change);
    }
}
