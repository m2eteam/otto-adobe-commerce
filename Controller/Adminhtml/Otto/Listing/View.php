<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Listing;

class View extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractListing
{
    private \M2E\Otto\Helper\Data\GlobalData $globalData;
    private \M2E\Otto\Helper\Data\Session $sessionHelper;
    private \M2E\Otto\Model\Listing\Repository $listingRepository;
    private \M2E\Otto\Model\Channel\Magento\Product\RuleFactory $productRuleFactory;
    private \M2E\Otto\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage;
    private \M2E\Otto\Model\Listing\Wizard\Repository $wizardRepository;
    private \M2E\Otto\Block\Adminhtml\Magento\Product\Rule\ViewStateFactory $viewStateFactory;
    private \M2E\Otto\Block\Adminhtml\Magento\Product\Rule\ViewState\Manager $viewStateManager;

    public function __construct(
        \M2E\Otto\Model\Listing\Repository $listingRepository,
        \M2E\Otto\Helper\Data\GlobalData $globalData,
        \M2E\Otto\Helper\Data\Session $sessionHelper,
        \M2E\Otto\Model\Channel\Magento\Product\RuleFactory $productRuleFactory,
        \M2E\Otto\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\Otto\Model\Listing\Wizard\Repository $wizardRepository,
        \M2E\Otto\Block\Adminhtml\Magento\Product\Rule\ViewStateFactory $viewStateFactory,
        \M2E\Otto\Block\Adminhtml\Magento\Product\Rule\ViewState\Manager $viewStateManager
    ) {
        parent::__construct();

        $this->globalData = $globalData;
        $this->sessionHelper = $sessionHelper;
        $this->listingRepository = $listingRepository;
        $this->productRuleFactory = $productRuleFactory;
        $this->uiListingRuntimeStorage = $uiListingRuntimeStorage;
        $this->wizardRepository = $wizardRepository;
        $this->viewStateFactory = $viewStateFactory;
        $this->viewStateManager = $viewStateManager;
    }

    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');

        $listing = $this->listingRepository->get($id);
        $this->uiListingRuntimeStorage->setListing($listing);

        if ($this->getRequest()->getQuery('ajax')) {
            // Set rule model
            // ---------------------------------------
            $this->setRuleData($listing);
            // ---------------------------------------

            $this->setAjaxContent(
                $this->getLayout()
                     ->createBlock(
                         \M2E\Otto\Block\Adminhtml\Otto\Listing\View::class,
                         '',
                         ['listing' => $listing],
                     )
                     ->getGridHtml(),
            );

            return $this->getResult();
        }

        if ((bool)$this->getRequest()->getParam('do_list', false)) {
            $this->sessionHelper->setValue(
                'products_ids_for_list',
                implode(',', $this->sessionHelper->getValue('added_products_ids')),
            );

            return $this->_redirect('*/*/*', [
                '_current' => true,
                'do_list' => null,
                'view_mode' => \M2E\Otto\Block\Adminhtml\Otto\Listing\View\Switcher::VIEW_MODE_OTTO,
            ]);
        }

        $id = $this->getRequest()->getParam('id');

        try {
            $listing = $this->listingRepository->get((int)$id);
        } catch (\M2E\Otto\Model\Exception\Logic $exception) {
            $this->getMessageManager()->addError(__('Listing does not exist.'));

            return $this->_redirect('*/otto_listing/index');
        }

        $this->uiListingRuntimeStorage->setListing($listing);

        $existWizard = $this->wizardRepository->findNotCompletedByListingAndType(
            $listing,
            \M2E\Otto\Model\Listing\Wizard::TYPE_GENERAL
        );

        if (($existWizard !== null) && (!$existWizard->isCompleted())) {
            $this->getMessageManager()->addNoticeMessage(
                __(
                    'Please make sure you finish adding new Products before moving to the next step.',
                ),
            );

            return $this->_redirect('*/listing_wizard/index', ['id' => $existWizard->getId()]);
        }

        // Set rule model
        // ---------------------------------------
        $this->setRuleData($listing);
        // ---------------------------------------

        $this->setPageHelpLink('https://docs-m2.m2epro.com/m2e-otto-shop-listings');

        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend(
                 (string)__(
                     '%extension_title Listing "%listing_title"',
                     [
                         'extension_title' => \M2E\Otto\Helper\Module::getExtensionTitle(),
                         'listing_title' => $listing->getTitle(),
                     ]
                 )
             );

        $this->addContent(
            $this->getLayout()->createBlock(
                \M2E\Otto\Block\Adminhtml\Otto\Listing\View::class,
            ),
        );

        return $this->getResult();
    }

    protected function setRuleData(\M2E\Otto\Model\Listing $listing): void
    {
        $prefix = sprintf(
            '%s_%s',
            \M2E\Otto\Model\Channel\Magento\Product\Rule::NICK,
            $listing->getId()
        );

        $state = $this->viewStateFactory->create($prefix);

        $getRuleBySessionData = function () use ($prefix, $listing) {
            return $this->createRuleBySessionData($prefix, $listing);
        };
        try {
            $ruleModel = $this->viewStateManager
                ->getRuleWithViewState(
                    $state,
                    \M2E\Otto\Model\Channel\Magento\Product\Rule::NICK,
                    $getRuleBySessionData,
                    $listing->getStoreId()
                );
        } catch (\M2E\Otto\Model\Exception\Logic $exception) {
            $ruleModel = $getRuleBySessionData();
            $state->reset();
            $state->setStateUnselect();
            $ruleModel->setViewSate($state);
        }

        $this->globalData->setValue('rule_model', $ruleModel);
    }

    private function createRuleBySessionData(
        string $prefix,
        \M2E\Otto\Model\Listing $listing
    ): \M2E\Otto\Model\Channel\Magento\Product\Rule {
        $this->globalData->setValue('rule_prefix', $prefix);

        $ruleModel = $this->productRuleFactory->create($prefix, $listing->getStoreId());

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
