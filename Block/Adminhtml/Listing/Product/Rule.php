<?php

namespace M2E\Otto\Block\Adminhtml\Listing\Product;

class Rule extends \M2E\Otto\Block\Adminhtml\Magento\Form\AbstractForm
{
    protected $_isShowHideProductsOption = false;

    private \M2E\Otto\Block\Adminhtml\AdvancedFilter\AbstractRenderer $advancedFilterRenderer;
    private string $searchBtnHtml;
    private string $resetBtnHtml;

    private \M2E\Otto\Helper\Data\GlobalData $globalDataHelper;
    private \M2E\Otto\Block\Adminhtml\AdvancedFilter\RendererFactory $rendererFactory;

    public function __construct(
        \M2E\Otto\Block\Adminhtml\AdvancedFilter\RendererFactory $rendererFactory,
        \M2E\Otto\Helper\Data\GlobalData $globalDataHelper,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->rendererFactory = $rendererFactory;
        $this->globalDataHelper = $globalDataHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('listingProductRule');
        // ---------------------------------------

        /** @var \M2E\Otto\Model\Magento\Product\Rule $ruleModel */
        $ruleModel = $this->globalDataHelper->getValue('rule_model');
        $this->advancedFilterRenderer = $this->getRenderer($ruleModel);
    }

    public function setShowHideProductsOption($isShow = true)
    {
        $this->_isShowHideProductsOption = $isShow;

        return $this;
    }

    public function isShowHideProductsOption()
    {
        return $this->_isShowHideProductsOption;
    }

    public function setSearchBtnHtml(string $searchBtnHtml): void
    {
        $this->searchBtnHtml = $searchBtnHtml;
    }

    public function setResetBtnHtml(string $resetBtnHtml): void
    {
        $this->resetBtnHtml = $resetBtnHtml;
    }

    protected function _prepareLayout()
    {
        $this->css->add(
            <<<CSS

        #rule_form .field-advanced_filter .admin__field-control:first-child {
            width: calc( 100% - 30px );
        }

        .advanced-filter-fieldset {
            border-top: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
            margin-top: -12px;
            padding-top: 12px;
            margin-bottom: 1em;
            display: none;
        }

        .advanced-filter-fieldset-active {
            margin-top: 1em;
        }

        .advanced-filter-fieldset {
            clear: both;
        }

        .advanced-filter-fieldset > legend.legend {
            border-bottom: none !important;
            margin-bottom: 5px !important;
        }

        .advanced-filter-fieldset .field-advanced_filter {
            margin-bottom: 1.5em !important;
            float: left;
            min-width: 50%;
        }

        .advanced-filter-fieldset .rule-param .label {
            font-size: 14px;
            font-weight: 600;
        }

        .advanced-filter-fieldset ul.rule-param-children {
            margin-top: 1em;
        }

        .advanced-filter-fieldset .data-grid {
            overflow: hidden;
        }

        .advanced-filter-fieldset .rule-chooser {
            margin: 20px 0;
        }
CSS
        );

        $this->advancedFilterRenderer->addCss($this->css);

        return parent::_prepareLayout();
    }

    protected function _beforeToHtml()
    {
        $this->js->add(
            <<<JS
    require([
        'M2ECore/Plugin/Messages'
    ], function(MessageObj) {
       MessageObj.clear();
    });
JS
        );

        $this->advancedFilterRenderer->renderJs(
            $this->js,
            $this->jsUrl,
            $this->jsTranslator
        );

        return parent::_beforeToHtml();
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'rule_form',
                'action' => 'javascript:void(0)',
                'method' => 'post',
                'enctype' => 'multipart/form-data',
                'onsubmit' => $this->getGridJsObjectName() . '.doFilter(event)',
            ],
        ]);

        $fieldset = $form->addFieldset(
            'listing_product_rules',
            [
                'legend' => '',
                'collapsable' => false,
                'class' => 'advanced-filter-fieldset',
            ]
        );

        $fieldset->addField(
            'advanced_filter',
            self::CUSTOM_CONTAINER,
            [
                'text' => $this->advancedFilterRenderer->renderHtml(
                    $this->searchBtnHtml,
                    $this->resetBtnHtml
                ),
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    private function getRenderer(
        \M2E\Otto\Model\Magento\Product\Rule $ruleModel
    ): \M2E\Otto\Block\Adminhtml\AdvancedFilter\AbstractRenderer {
        if (!$ruleModel->isExistsViewSate()) {
            throw new \LogicException('View state must be set');
        }

        $viewState = $ruleModel->getViewState();

        if ($viewState->isStateCreation()) {
            return $this->rendererFactory->createCreatingRenderer(
                $viewState->getViewKey(),
                $ruleModel,
                $this->getLayout()
            );
        }

        if ($viewState->isStateUpdating()) {
            return $this->rendererFactory->createUpdatingRenderer(
                $viewState->getUpdatedEntityId(),
                $viewState->getViewKey(),
                $ruleModel,
                $this->getLayout()
            );
        }

        if ($viewState->isStateSelected()) {
            return $this->rendererFactory->createSelectedRenderer(
                $viewState->getSelectedEntityId(),
                $viewState->getIsEntityRecentlyCreated(true),
                $this->getLayout()
            );
        }

        if ($viewState->isStateUnselected()) {
            return $this->rendererFactory->createUnselectedRenderer(
                $ruleModel->getNick(),
                $this->getLayout()
            );
        }

        throw new \LogicException('Unresolved View State');
    }
}
