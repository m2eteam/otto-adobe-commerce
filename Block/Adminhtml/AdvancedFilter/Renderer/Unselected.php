<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\AdvancedFilter\Renderer;

class Unselected extends \M2E\Otto\Block\Adminhtml\AdvancedFilter\AbstractRenderer
{
    use PrepareSelectTrait;

    private string $ruleModelNick;
    private \M2E\Otto\Model\AdvancedFilter\Repository $repository;

    public function __construct(
        string $ruleModelNick,
        \M2E\Otto\Model\AdvancedFilter\Repository $repository,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);

        $this->repository = $repository;
        $this->ruleModelNick = $ruleModelNick;
    }

    public function renderJs(
        \M2E\Otto\Block\Adminhtml\Magento\Renderer\JsRenderer $js,
        \M2E\Otto\Block\Adminhtml\Magento\Renderer\JsUrlRenderer $jsUrl,
        \M2E\Otto\Block\Adminhtml\Magento\Renderer\JsTranslatorRenderer $jsTranslator
    ): void {
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
        $entities = $this->repository->findItemsByModelNick($this->ruleModelNick);

        $values = $this->createSelect($entities);

        $addNew = __('Add New');

        $element = $this->_formFactory->create()->addField(
            'advanced_filter_list',
            self::SELECT,
            [
                'name' => 'rule_entity_id',
                'label' => __('Saved Filter'),
                'class' => 'advanced-filter-select',
                'values' => $values,
                'value' => null,
                'after_element_html' => <<<HTML
&nbsp;
<span style="line-height: 30px;">
    <a href="javascript: void(0);" onclick="AdvancedFilterSelectObj.createNewFilter()">{$addNew}</a>
</span>
HTML
                ,
            ]
        );

        return sprintf('<div class="advanced-filter-select-container">%s</div>', $element->toHtml());
    }
}
