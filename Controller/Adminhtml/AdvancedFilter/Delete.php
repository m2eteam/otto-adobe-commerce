<?php

namespace M2E\Otto\Controller\Adminhtml\AdvancedFilter;

class Delete extends \M2E\Otto\Controller\Adminhtml\AbstractBase
{
    private \M2E\Otto\Block\Adminhtml\Magento\Product\Rule\ViewStateFactory $viewStateFactory;
    private \M2E\Otto\Model\AdvancedFilter\Repository $repository;

    public function __construct(
        \M2E\Otto\Block\Adminhtml\Magento\Product\Rule\ViewStateFactory $viewStateFactory,
        \M2E\Otto\Model\AdvancedFilter\Repository $repository,
        \M2E\Otto\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->viewStateFactory = $viewStateFactory;
        $this->repository = $repository;
    }

    public function execute()
    {
        $ruleEntityId = $this->getRequest()->getPostValue('rule_entity_id');
        $viewStateKey = $this->getRequest()->getPostValue('view_state_key');
        if (empty($ruleEntityId) || empty($viewStateKey)) {
            throw new \Exception('Invalid input');
        }

        $advancedFilter = $this->repository->getAdvancedFilter((int)$ruleEntityId);
        $modelNick = $advancedFilter->getModelNick();
        $this->repository->remove($advancedFilter);

        $viewState = $this->viewStateFactory->create($viewStateKey);
        if (!$this->repository->isExistItemsWithModelNick($modelNick)) {
            $viewState->setStateCreation();
        } else {
            $viewState->setStateUnselect();
        }

        return $this->getResult();
    }
}
