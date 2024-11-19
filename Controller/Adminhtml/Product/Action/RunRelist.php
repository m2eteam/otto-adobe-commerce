<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Product\Action;

use M2E\Otto\Controller\Adminhtml\Product\Action\ActionService;
use M2E\Otto\Controller\Adminhtml\Product\Action\ActionTrait;

class RunRelist extends \M2E\Otto\Controller\Adminhtml\Otto\Listing\AbstractAction
{
    use ActionTrait;

    private \M2E\Otto\Model\Product\Repository $productRepository;
    private \M2E\Otto\Model\ResourceModel\Product\Grid\AllItems\ActionFilter $massActionFilter;
    /** @var \M2E\Otto\Controller\Adminhtml\Product\Action\ActionService */
    private ActionService $actionService;

    public function __construct(
        \M2E\Otto\Controller\Adminhtml\Product\Action\ActionService $actionService,
        \M2E\Otto\Model\ResourceModel\Product\Grid\AllItems\ActionFilter $massActionFilter,
        \M2E\Otto\Model\Product\Repository $productRepository,
        \M2E\Otto\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\Otto\Model\Listing\LogService $listingLogService
    ) {
        parent::__construct($listingProductCollectionFactory, $listingLogService);

        $this->productRepository = $productRepository;
        $this->massActionFilter = $massActionFilter;
        $this->actionService = $actionService;
    }

    public function execute()
    {
        $products = $this->productRepository->massActionSelectedProducts($this->massActionFilter);

        if ($this->isRealtimeAction($products)) {
            ['result' => $result] = $this->actionService->runRelist($products);
            if ($result === 'success') {
                $this->getMessageManager()->addSuccessMessage(
                    __('"Relisting Selected Items On Otto" task has completed.'),
                );
            } else {
                $this->getMessageManager()->addErrorMessage(
                    __('"Relisting Selected Items On Otto" task has completed with errors.'),
                );
            }

            return $this->redirectToGrid();
        }

        $this->actionService->scheduleRelist($products);

        $this->getMessageManager()->addSuccessMessage(
            __('"Relisting Selected Items On Otto" task has completed.'),
        );

        return $this->redirectToGrid();
    }
}