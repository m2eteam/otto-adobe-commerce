<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Magento\Product\Rule\ViewState;

class Manager
{
    private \M2E\Otto\Model\AdvancedFilter\Repository $repository;
    private \M2E\Otto\Model\AdvancedFilter\Manager $ruleManager;
    private \Magento\Framework\App\RequestInterface $request;

    public function __construct(
        \M2E\Otto\Model\AdvancedFilter\Repository $repository,
        \M2E\Otto\Model\AdvancedFilter\Manager $ruleManager,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->repository = $repository;
        $this->ruleManager = $ruleManager;
        $this->request = $request;
    }

    public function getRuleWithViewState(
        \M2E\Otto\Block\Adminhtml\Magento\Product\Rule\ViewState $viewState,
        string $ruleModelNick,
        callable $getRuleBySessionData,
        ?int $storeId = null
    ): \M2E\Otto\Model\Magento\Product\Rule {

        // State - Creation
        //  ---------------------------------------------
        if ($viewState->isWithoutState()) {
            if ($this->repository->isExistItemsWithModelNick($ruleModelNick)) {
                return $this->getRuleWithUnselectedState($viewState, $ruleModelNick, $storeId);
            }
            $viewState->setStateCreation();
        }

        if ($this->request->getParam('create_new_filter')) {
            $viewState->setStateCreation();
            $viewState->setIsShowRuleBlock(true);
        }

        if ($viewState->isStateCreation() && $this->request->getParam('creating_back')) {
            return $this->getRuleWithUnselectedState($viewState, $ruleModelNick, $storeId);
        }

        if ($viewState->isStateCreation()) {
            $rule = $getRuleBySessionData();
            $rule->setViewSate($viewState);

            return $rule;
        }

        // State - Updating
        //  ---------------------------------------------

        if (
            $this->request->getParam('rule_entity_id')
            && $this->request->getParam('rule_updating')
        ) {
            $viewState->setStateUpdating((int)$this->request->getParam('rule_entity_id'));
        }

        if ($viewState->isStateUpdating() && $this->request->getParam('is_reset')) {
            $rule = $this->ruleManager->getRuleModelByNick($ruleModelNick, $storeId);
            $rule->setViewSate($viewState);

            return $rule;
        }

        if ($viewState->isStateUpdating() && $this->request->getParam('updating_back')) {
            return $this->getRuleWithUnselectedState($viewState, $ruleModelNick, $storeId);
        }

        if ($viewState->isStateUpdating()) {
            $rule = $this->ruleManager->getRuleWithSavedConditions(
                $viewState->getUpdatedEntityId(),
                $ruleModelNick,
                $storeId
            );
            $viewState->setIsShowRuleBlock(true);
            $rule->setViewSate($viewState);

            return $rule;
        }

        // State - Selected
        //  ---------------------------------------------

        if ($this->request->getParam('rule_entity_id')) {
            $viewState->setStateSelect((int)$this->request->getParam('rule_entity_id'));
        }

        if ($viewState->isStateSelected() && $this->request->getParam('is_reset')) {
            return $this->getRuleWithUnselectedState($viewState, $ruleModelNick, $storeId);
        }

        if ($viewState->isStateSelected()) {
            $rule = $this->ruleManager->getRuleWithSavedConditions(
                $viewState->getSelectedEntityId(),
                $ruleModelNick,
                $storeId
            );

            $viewState->setIsShowRuleBlock(true);
            $rule->setViewSate($viewState);

            return $rule;
        }

        // State - Unselected
        //  ---------------------------------------------

        if ($viewState->isStateUnselected()) {
            return $this->getRuleWithUnselectedState($viewState, $ruleModelNick, $storeId);
        }

        //  ---------------------------------------------

        throw new \LogicException('Unresolved View State');
    }

    private function getRuleWithUnselectedState(
        \M2E\Otto\Block\Adminhtml\Magento\Product\Rule\ViewState $viewState,
        string $ruleModelNick,
        ?int $storeId = null
    ): \M2E\Otto\Model\Magento\Product\Rule {
        $viewState->setStateUnselect();
        $rule = $this->ruleManager->getRuleModelByNick($ruleModelNick, $storeId);
        $rule->setViewSate($viewState);

        return $rule;
    }
}
