<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order;

class Cancel
{
    private ChangeCreateService $changeCreateService;
    private Item\Repository $orderItemRepository;

    public function __construct(
        \M2E\Otto\Model\Order\ChangeCreateService $changeCreateService,
        \M2E\Otto\Model\Order\Item\Repository $orderItemRepository
    ) {
        $this->changeCreateService = $changeCreateService;
        $this->orderItemRepository = $orderItemRepository;
    }

    public function process(
        \M2E\Otto\Model\Order $order,
        \Magento\Sales\Model\Order\Creditmemo $creditmemo,
        int $initiator
    ): void {
        try {
            \M2E\Core\Helper\Data::validateInitiator($initiator);
            $this->validateOrder($order);
            $itemsForCancellation = $this->getItemsForCancellation($order, $creditmemo);
        } catch (\M2E\Otto\Model\Order\Exception\OrderCannotBeCancelledException $exception) {
            return;
        }

        $this->changeCreateService->create(
            (int)$order->getId(),
            null,
            \M2E\Otto\Model\Order\Change::ACTION_CANCEL,
            $initiator,
            [
                Change::PARAMS_KEY_ORDER_ITEMS => array_map(function ($item) {
                    return ['id' => $item->getId()];
                }, $itemsForCancellation),
            ],
        );

        foreach ($itemsForCancellation as $item) {
            $item->setIsCancellationInProgressYes();
            $this->orderItemRepository->save($item);
        }
    }

    /**
     * @throws \M2E\Otto\Model\Order\Exception\OrderCannotBeCancelledException
     */
    private function validateOrder(\M2E\Otto\Model\Order $order): void
    {
        if (!$order->canCancel()) {
            $this->orderCannotBeCancelledException('Order cannot be cancelled.');
        }

        if (!$order->getAccount()->getOrdersSettings()->isCancelChannelOrderIfCreditmemoCreatedEnabled()) {
            $this->orderCannotBeCancelledException(
                'Setting "OTTO > Configuration > Accounts > Order > Cancel Otto Order" disabled.'
            );
        }
    }

    /**
     * @return \M2E\Otto\Model\Order\Item[]
     * @throws \M2E\Otto\Model\Order\Exception\OrderCannotBeCancelledException
     */
    private function getItemsForCancellation(
        \M2E\Otto\Model\Order $order,
        \Magento\Sales\Model\Order\Creditmemo $creditmemo
    ): array {
        $items = [];
        foreach ($creditmemo->getItems() as $creditmemoItem) {
            $ottoOrderItems = $this->loadItems($creditmemoItem, $order);
            if (empty($ottoOrderItems)) {
                continue;
            }

            array_push($items, ...$ottoOrderItems);
        }

        if (empty($items)) {
            $this->orderCannotBeCancelledException('Not found any order items.');
        }

        return $items;
    }

    /**
     * @return \M2E\Otto\Model\Order\Item[]
     */
    private function loadItems(
        \Magento\Sales\Api\Data\CreditmemoItemInterface $creditmemoItem,
        \M2E\Otto\Model\Order $order
    ): array {
        $magentoProductId = (int)$creditmemoItem->getProductId();
        $qty = (int)$creditmemoItem->getQty();

        $result = [];
        foreach ($order->getItems() as $item) {
            if ($qty === 0) {
                break;
            }

            if ($magentoProductId !== $item->getMagentoProductId()) {
                continue;
            }

            if (
                !$item->isReadyForCancellation()
                || $item->isCancellationInProgress()
            ) {
                continue;
            }

            $result[] = $item;
            $qty--;
        }

        return $result;
    }

    private function orderCannotBeCancelledException(string $reason): void
    {
        throw new \M2E\Otto\Model\Order\Exception\OrderCannotBeCancelledException($reason);
    }
}
