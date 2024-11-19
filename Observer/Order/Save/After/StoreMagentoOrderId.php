<?php

namespace M2E\Otto\Observer\Order\Save\After;

class StoreMagentoOrderId extends \M2E\Otto\Observer\AbstractObserver
{
    protected function process(): void
    {
        /** @var \Magento\Sales\Model\Order $magentoOrder */
        $magentoOrder = $this->getEvent()->getOrder();

        /** @var \M2E\Otto\Model\Order $order */
        $order = $this
            ->getHelper('Data\GlobalData')
            ->getValue(\M2E\Otto\Model\Order::ADDITIONAL_DATA_KEY_IN_ORDER);
        $this->getHelper('Data\GlobalData')
             ->unsetValue(\M2E\Otto\Model\Order::ADDITIONAL_DATA_KEY_IN_ORDER);

        if (empty($order)) {
            return;
        }

        if ($order->getMagentoOrderId() == $magentoOrder->getId()) {
            return;
        }

        $order->addData([
            'magento_order_id' => $magentoOrder->getId(),
            'magento_order_creation_failure' => \M2E\Otto\Model\Order::MAGENTO_ORDER_CREATION_FAILED_NO,
            'magento_order_creation_latest_attempt_date' => \M2E\Otto\Helper\Date::createCurrentGmt()
                                                                                       ->format('Y-m-d H:i:s'),
        ]);

        $order->setMagentoOrder($magentoOrder);
        $order->save();
    }
}