<?php

namespace M2E\Otto\Controller\Adminhtml\Otto\Order;

class View extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractOrder
{
    private \M2E\Otto\Model\Order\Repository $repository;

    public function __construct(
        \M2E\Otto\Model\Order\Repository $repository
    ) {
        parent::__construct();
        $this->repository = $repository;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $order = $this->repository->get((int)$id);

        $this->addContent(
            $this->getLayout()->createBlock(
                \M2E\Otto\Block\Adminhtml\Otto\Order\View::class,
                '',
                ['order' => $order]
            )
        );

        $this->init();
        $this->getResultPage()->getConfig()->getTitle()->prepend(__('View Order Details'));

        return $this->getResult();
    }
}
