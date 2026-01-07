<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\AdvancedFilter\Renderer;

class Updating extends \M2E\Otto\Block\Adminhtml\AdvancedFilter\AbstractRenderer
{
    private int $updatedEntityId;
    private string $viewStateKey;
    private \M2E\Otto\Model\Magento\Product\Rule $ruleModel;
    private \M2E\Otto\Model\AdvancedFilter\Repository $repository;

    public function __construct(
        int $updatedEntityId,
        string $viewStateKey,
        \M2E\Otto\Model\Magento\Product\Rule $ruleModel,
        \M2E\Otto\Model\AdvancedFilter\Repository $repository,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);

        $this->updatedEntityId = $updatedEntityId;
        $this->viewStateKey = $viewStateKey;
        $this->ruleModel = $ruleModel;
        $this->repository = $repository;
    }

    public function renderJs(
        \M2E\Otto\Block\Adminhtml\Magento\Renderer\JsRenderer $js,
        \M2E\Otto\Block\Adminhtml\Magento\Renderer\JsUrlRenderer $jsUrl,
        \M2E\Otto\Block\Adminhtml\Magento\Renderer\JsTranslatorRenderer $jsTranslator
    ): void {
        $jsUrl->add(
            $this->getUrl('*/advancedFilter/update'),
            'listing_product_advanced_filter/update'
        );
        $jsUrl->add(
            $this->getUrl('*/advancedFilter/delete'),
            'listing_product_advanced_filter/delete'
        );

        $js->addRequireJs(
            ['af' => 'Otto/AdvancedFilter/Updating'],
            <<<JS
            window.AdvancedFilterUpdatingObj = new AdvancedFilterUpdating();
            AdvancedFilterUpdatingObj.init(
                '{$this->updatedEntityId}',
                '{$this->viewStateKey}',
                '{$this->ruleModel->getPrefix()}',
            );
JS
        );
    }

    public function renderHtml(string $searchBtnHtml, string $resetBtnHtml): string
    {
        $ruleBlock = $this->getLayout()
                          ->createBlock(\M2E\Otto\Block\Adminhtml\Magento\Product\Rule::class)
                          ->setData(['rule_model' => $this->ruleModel]);

        /** @var \M2E\Otto\Block\Adminhtml\Magento\Button $btn */
        $updateFilterBtn = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Magento\Button::class);
        $updateFilterBtn->setData([
            'label' => __('Update Filter'),
            'class' => 'action-default scalable action-primary',
            'onclick' => 'AdvancedFilterUpdatingObj.openUpdateFilterPopup()',
        ]);

        /** @var \M2E\Otto\Block\Adminhtml\Magento\Button $backBtn */
        $backBtn = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Magento\Button::class);
        $backBtn->setData([
            'label' => __('Back'),
            'class' => 'action-default scalable action-primary',
            'onclick' => 'AdvancedFilterUpdatingObj.back()',
        ]);

        /** @var \M2E\Otto\Block\Adminhtml\Magento\Button $deleteBtn */
        $deleteBtn = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Magento\Button::class);
        $deleteBtn->setData([
            'label' => __('Delete'),
            'class' => 'action-default scalable action-tertiary',
            'onclick' => 'AdvancedFilterUpdatingObj.delete()',
        ]);

        $buttons = $this->wrapFilterHtmlBtn(
            $backBtn->toHtml()
            . $resetBtnHtml
            . $updateFilterBtn->toHtml()
            . $deleteBtn->toHtml()
        );

        return $ruleBlock->toHtml() . $buttons . $this->getModalHtml();
    }

    private function getModalHtml(): string
    {
        $entity = $this->repository->getAdvancedFilter($this->updatedEntityId);

        $form = $this->_formFactory->create();
        $nameInput = $form->addField(
            'advanced_filter_name_input_update',
            'text',
            [
                'name' => 'filter_name',
                'label' => __('Filter Name'),
                'class' => 'advanced-filter-name',
                'value' => $entity->getTitle(),
            ]
        );

        return '<div id="update_filter_popup_content" class="hidden advanced-filter-popup">'
            . $nameInput->toHtml()
            . '</div>';
    }
}
