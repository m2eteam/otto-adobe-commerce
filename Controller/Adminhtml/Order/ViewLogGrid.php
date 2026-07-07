<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Order;

class ViewLogGrid extends \M2E\Otto\Controller\Adminhtml\AbstractOrder
{
    private \M2E\Otto\Model\Order\Repository $orderRepository;

    public function __construct(
        \M2E\Otto\Model\Order\Repository $orderRepository,
        \M2E\Otto\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        $orderId = $this->getRequest()->getParam('id');
        $order = $this->orderRepository->get($orderId);

        $orderLogGrid = $this->getLayout()->createBlock(
            \M2E\Otto\Block\Adminhtml\Order\View\Log\Grid::class,
            '',
            ['order' => $order]
        );

        $this->setAjaxContent($orderLogGrid->toHtml());

        return $this->getResult();
    }
}
