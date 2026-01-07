<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\AdvancedFilter\Renderer;

class Selected extends \M2E\Otto\Block\Adminhtml\AdvancedFilter\AbstractRenderer
{
    use PrepareSelectTrait;

    private int $selectedRuleId;
    private bool $isRuleRecentlyCreated;
    private \M2E\Otto\Model\AdvancedFilter\Repository $repository;

    public function __construct(
        int $selectedRuleId,
        bool $isRuleRecentlyCreated,
        \M2E\Otto\Model\AdvancedFilter\Repository $repository,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);

        $this->selectedRuleId = $selectedRuleId;
        $this->repository = $repository;
        $this->isRuleRecentlyCreated = $isRuleRecentlyCreated;
    }

    public function renderJs(
        \M2E\Otto\Block\Adminhtml\Magento\Renderer\JsRenderer $js,
        \M2E\Otto\Block\Adminhtml\Magento\Renderer\JsUrlRenderer $jsUrl,
        \M2E\Otto\Block\Adminhtml\Magento\Renderer\JsTranslatorRenderer $jsTranslator
    ): void {
        if ($this->isRuleRecentlyCreated) {
            $js->add(
                <<<JS
    require([
        'M2ECore/Plugin/Messages',
        'mage/translate'
    ], function(MessageObj, \$t) {
       MessageObj.clear();
       MessageObj.addSuccess(\$t('New filter have been saved'));
    });
JS
            );
        }

        $js->addRequireJs(
            ['select' => 'Otto/AdvancedFilter/Select'],
            <<<JS
            window.AdvancedFilterSelectObj = new AdvancedFilterSelect();
            AdvancedFilterSelectObj.init();
            AdvancedFilterSelectObj.initEvents();
JS
        );
    }

    public function renderHtml(string $searchBtnHtml, string $resetBtnHtml): string
    {
        $buttons = $this->wrapFilterHtmlBtn($searchBtnHtml . $resetBtnHtml);

        return $this->getFilterSelectHtml() . $buttons;
    }

    private function getFilterSelectHtml(): string
    {
        $ruleEntity = $this->repository->getAdvancedFilter($this->selectedRuleId);
        $entities = $this->repository->findItemsByModelNick($ruleEntity->getModelNick());

        $values = $this->createSelect($entities);

        $view = __('View');
        $edit = __('Edit');
        $or = __('or');
        $addNew = __('Add New');

        $element = $this->_formFactory->create()->addField(
            'advanced_filter_list',
            self::SELECT,
            [
                'name' => 'rule_entity_id',
                'label' => __('Saved Filter'),
                'values' => $values,
                'value' => $this->selectedRuleId,
                'class' => 'advanced-filter-select',
                'after_element_html' => <<<HTML
&nbsp;
<span style="line-height: 30px;">
    <a href="javascript: void(0);" style="" onclick="AdvancedFilterSelectObj.updateFilter();">
        {$view}&nbsp;/&nbsp;{$edit}
    </a>
    <span>{$or}</span>
    <a href="javascript: void(0);" onclick="AdvancedFilterSelectObj.createNewFilter()">{$addNew}</a>
</span>
HTML
                ,
            ]
        );

        return sprintf('<div class="advanced-filter-select-container">%s</div>', $element->toHtml());
    }
}
