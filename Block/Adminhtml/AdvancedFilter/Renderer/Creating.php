<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\AdvancedFilter\Renderer;

class Creating extends \M2E\Otto\Block\Adminhtml\AdvancedFilter\AbstractRenderer
{
    private string $viewStateKey;
    private \M2E\Otto\Model\Magento\Product\Rule $ruleModel;
    private \M2E\Otto\Model\AdvancedFilter\Repository $repository;

    public function __construct(
        string $viewStateKey,
        \M2E\Otto\Model\Magento\Product\Rule $ruleModel,
        \M2E\Otto\Model\AdvancedFilter\Repository $repository,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);

        $this->ruleModel = $ruleModel;
        $this->viewStateKey = $viewStateKey;
        $this->repository = $repository;
    }

    public function renderJs(
        \M2E\Otto\Block\Adminhtml\Magento\Renderer\JsRenderer $js,
        \M2E\Otto\Block\Adminhtml\Magento\Renderer\JsUrlRenderer $jsUrl,
        \M2E\Otto\Block\Adminhtml\Magento\Renderer\JsTranslatorRenderer $jsTranslator
    ): void {
        $jsUrl->add(
            $this->getUrl('*/advancedFilter/save'),
            'listing_product_advanced_filter/save'
        );

        $js->addRequireJs(
            ['creating' => 'Otto/AdvancedFilter/Creating'],
            <<<JS
            window.AdvancedFilterCreatingObj = new AdvancedFilterCreating();
            AdvancedFilterCreatingObj.init(
                '{$this->ruleModel->getNick()}',
                '{$this->ruleModel->getPrefix()}',
                '$this->viewStateKey'
            )
JS
        );
    }

    public function renderHtml(string $searchBtnHtml, string $resetBtnHtml): string
    {
        $ruleBlock = $this
            ->getLayout()
            ->createBlock(\M2E\Otto\Block\Adminhtml\Magento\Product\Rule::class)
            ->setData(['rule_model' => $this->ruleModel]);

        /** @var \M2E\Otto\Block\Adminhtml\Magento\Button $btn */
        $createFilterBtn = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Magento\Button::class);
        $createFilterBtn->setData([
            'label' => __('Save Filter'),
            'class' => 'action-default scalable action-primary',
            'onclick' => 'AdvancedFilterCreatingObj.openSaveFilterPopup()',
        ]);

        $backBtnHtml = '';
        if ($this->repository->isExistItemsWithModelNick($this->ruleModel->getNick())) {
            /** @var \M2E\Otto\Block\Adminhtml\Magento\Button $backBtn */
            $backBtn = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Magento\Button::class);
            $backBtn->setData([
                'label' => __('Back'),
                'class' => 'action-default scalable action-primary',
                'onclick' => 'AdvancedFilterCreatingObj.back()',
            ]);
            $backBtnHtml = $backBtn->toHtml();
        }

        $buttons = $this->wrapFilterHtmlBtn(
            $backBtnHtml
            . $searchBtnHtml
            . $resetBtnHtml
            . $createFilterBtn->toHtml()
        );

        return $ruleBlock->toHtml() . $buttons . $this->getModalHtml();
    }

    private function getModalHtml(): string
    {
        $form = $this->_formFactory->create();
        $nameInput = $form->addField(
            'advanced_filter_name_input_create',
            'text',
            [
                'name' => 'filter_name',
                'class' => 'advanced-filter-name',
                'label' => __('Filter Name'),
            ]
        );

        return '<div id="new_filter_popup_content" class="hidden advanced-filter-popup">'
            . $nameInput->toHtml()
            . '</div>';
    }
}
