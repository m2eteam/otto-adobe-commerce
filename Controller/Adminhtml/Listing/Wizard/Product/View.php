<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Listing\Wizard\Product;

use M2E\Otto\Model\Listing\Wizard\StepDeclarationCollectionFactory;

class View extends \M2E\Otto\Controller\Adminhtml\Listing\Wizard\StepAbstract
{
    use \M2E\Otto\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\Otto\Helper\Data\GlobalData $globalDataHelper;
    private \M2E\Otto\Model\Magento\Product\RuleFactory $magentoProductRuleFactory;
    private \M2E\Otto\Model\Listing\Wizard\Manager $manager;
    private \M2E\Otto\Helper\Data\Session $sessionHelper;
    private \M2E\Otto\Block\Adminhtml\Magento\Product\Rule\ViewStateFactory $viewStateFactory;
    private \M2E\Otto\Block\Adminhtml\Magento\Product\Rule\ViewState\Manager $viewStateManager;

    public function __construct(
        \M2E\Otto\Helper\Data\Session $sessionHelper,
        \M2E\Otto\Helper\Data\GlobalData $globalDataHelper,
        \M2E\Otto\Model\Magento\Product\RuleFactory $magentoProductRuleFactory,
        \M2E\Otto\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\Otto\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\Otto\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage,
        \M2E\Otto\Block\Adminhtml\Magento\Product\Rule\ViewStateFactory $viewStateFactory,
        \M2E\Otto\Block\Adminhtml\Magento\Product\Rule\ViewState\Manager $viewStateManager
    ) {
        parent::__construct($wizardManagerFactory, $uiListingRuntimeStorage, $uiWizardRuntimeStorage);

        $this->sessionHelper = $sessionHelper;
        $this->globalDataHelper = $globalDataHelper;
        $this->magentoProductRuleFactory = $magentoProductRuleFactory;
        $this->viewStateFactory = $viewStateFactory;
        $this->viewStateManager = $viewStateManager;
    }

    protected function getStepNick(): string
    {
        return StepDeclarationCollectionFactory::STEP_SELECT_PRODUCTS;
    }

    protected function process(\M2E\Otto\Model\Listing $listing)
    {
        $this->manager = $this->getWizardManager();

        $data = $this->manager->getStepData(StepDeclarationCollectionFactory::STEP_SELECT_PRODUCT_SOURCE);

        $source = $data['source'];

        if ($source === \M2E\Otto\Block\Adminhtml\Listing\Wizard\ProductSourceSelect::MODE_PRODUCT) {
            return $this->showGridByCatalog(
                $listing,
                $source,
            );
        }

        if ($source === \M2E\Otto\Block\Adminhtml\Listing\Wizard\ProductSourceSelect::MODE_CATEGORY) {
            return $this->showGridByCategories(
                $listing,
                $source,
            );
        }

        throw new \LogicException('Unknown source type.');
    }

    private function showGridByCatalog(\M2E\Otto\Model\Listing $listing, string $source)
    {
        $this->setRuleData($listing);

        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->setAjaxContent(
                $this->getLayout()
                     ->createBlock(
                         \M2E\Otto\Block\Adminhtml\Listing\Wizard\Product\Add\Grid::class,
                     )
                     ->toHtml(),
            );

            return $this->getResult();
        }

        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend(__('Select Magento Products'));

        $this->addContent(
            $this->getLayout()->createBlock(
                \M2E\Otto\Block\Adminhtml\Listing\Wizard\Product\Add::class,
                '',
                [
                    'sourceMode' => $source,
                ],
            ),
        );

        return $this->getResult();
    }

    private function showGridByCategories(\M2E\Otto\Model\Listing $listing, string $source)
    {
        $this->setRuleData($listing);

        $data = $this->manager->getStepData($this->getStepNick());
        $selectedProductsIds = $data['products_ids'] ?? [];

        if ($this->getRequest()->isXmlHttpRequest()) {
            if ($this->getRequest()->getParam('current_category_id')) {
                $data['current_category_id'] = $this->getRequest()->getParam('current_category_id');

                $this->manager->setStepData($this->getStepNick(), $data);
            }

            /** @var \M2E\Otto\Block\Adminhtml\Listing\Wizard\Product\Add\Category\Grid $grid */
            $grid = $this->getLayout()
                         ->createBlock(
                             \M2E\Otto\Block\Adminhtml\Listing\Wizard\Product\Add\Category\Grid::class,
                         );

            $grid->setSelectedIds($selectedProductsIds);
            $grid->setCurrentCategoryId($data['current_category_id']);

            $this->setAjaxContent($grid->toHtml());

            return $this->getResult();
        }

        $this->setPageHelpLink('https://docs-m2.m2epro.com/add-products-to-m2e-otto-listing');

        $this->getResultPage()
             ->getConfig()
             ->getTitle()->prepend(__('Select Magento Products'));

        /** @var \M2E\Otto\Block\Adminhtml\Listing\Wizard\Product\Add $gridContainer */
        $gridContainer = $this->getLayout()->createBlock(
            \M2E\Otto\Block\Adminhtml\Listing\Wizard\Product\Add::class,
            '',
            [
                'sourceMode' => $source,
            ],
        );
        $this->addContent($gridContainer);

        /** @var \M2E\Otto\Block\Adminhtml\Listing\Wizard\Category\Add\Tree $treeBlock */
        $treeBlock = $this->getLayout()
                          ->createBlock(
                              \M2E\Otto\Block\Adminhtml\Listing\Wizard\Category\Add\Tree::class,
                          );

        if (empty($data['current_category_id'])) {
            $currentNode = $treeBlock->getRoot()->getChildren()->getIterator()->current();
            if (!$currentNode) {
                throw new \M2E\Otto\Model\Exception('No Categories found');
            }

            $data['current_category_id'] = $currentNode->getId();
            $this->manager->setStepData($this->getStepNick(), $data);
        }

        $treeBlock->setGridId($gridContainer->getChildBlock('grid')->getId());
        $treeBlock->setSelectedIds($selectedProductsIds);
        $treeBlock->setCurrentNodeById($data['current_category_id']);

        $gridContainer->getChildBlock('grid')->setTreeBlock($treeBlock);
        $gridContainer->getChildBlock('grid')->setSelectedIds($selectedProductsIds);
        $gridContainer->getChildBlock('grid')->setCurrentCategoryId($data['current_category_id']);

        return $this->getResult();
    }

    private function setRuleData(\M2E\Otto\Model\Listing $listing): void
    {
        $prefix = sprintf(
            '%s_%s',
            \M2E\Otto\Model\Magento\Product\Rule::NICK,
            $listing->getId()
        );

        $state = $this->viewStateFactory->create($prefix);
        $getRuleBySessionDataCallback = function () use ($prefix, $listing) {
            return $this->createRuleBySessionData($prefix, $listing);
        };

        try {
            $ruleModel = $this->viewStateManager
                ->getRuleWithViewState(
                    $state,
                    \M2E\Otto\Model\Magento\Product\Rule::NICK,
                    $getRuleBySessionDataCallback,
                    $listing->getStoreId()
                );
        } catch (\M2E\Otto\Model\Exception\Logic $exception) {
            $ruleModel = $getRuleBySessionDataCallback();
            $state->reset();
            $state->setStateUnselect();
            $ruleModel->setViewSate($state);
        }

        $this->globalDataHelper->setValue('rule_model', $ruleModel);
    }

    private function createRuleBySessionData(
        string $prefix,
        \M2E\Otto\Model\Listing $listing
    ): \M2E\Otto\Model\Magento\Product\Rule {
        $this->globalDataHelper->setValue('rule_prefix', $prefix);

        $ruleModel = $this->magentoProductRuleFactory->create($prefix, $listing->getStoreId());

        $ruleParam = $this->getRequest()->getPost('rule');
        if (!empty($ruleParam)) {
            $this->sessionHelper->setValue(
                $prefix,
                $ruleModel->getSerializedFromPost($this->getRequest()->getPostValue())
            );
        } elseif ($ruleParam !== null) {
            $this->sessionHelper->setValue($prefix, []);
        }

        $sessionRuleData = $this->sessionHelper->getValue($prefix);
        if (!empty($sessionRuleData)) {
            $ruleModel->loadFromSerialized($sessionRuleData);
        }

        return $ruleModel;
    }
}
