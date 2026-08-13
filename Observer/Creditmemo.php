<?php

declare(strict_types=1);

namespace M2E\Otto\Observer;

class Creditmemo extends AbstractObserver
{
    private \M2E\Otto\Model\Order\Repository $orderRepository;
    private \M2E\Otto\Model\Order\Cancel $orderCancel;

    public function __construct(
        \M2E\Otto\Model\Order\Repository $orderRepository,
        \M2E\Otto\Model\Order\Cancel $orderCancel
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderCancel = $orderCancel;
    }

    protected function process(): void
    {
        /** @var \Magento\Sales\Model\Order\Creditmemo $creditmemo */
        $creditmemo = $this->getEvent()->getCreditmemo();
        $order = $this->orderRepository->findByMagentoOrderId((int)$creditmemo->getOrderId());
        if ($order === null) {
            return;
        }

        $this->orderCancel->process($order, $creditmemo, \M2E\Core\Helper\Data::INITIATOR_USER);
    }
}
