<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\AdvancedFilter;

abstract class AbstractRenderer extends \M2E\Otto\Block\Adminhtml\Magento\Form\AbstractForm
{
    abstract public function renderJs(
        \M2E\Otto\Block\Adminhtml\Magento\Renderer\JsRenderer $js,
        \M2E\Otto\Block\Adminhtml\Magento\Renderer\JsUrlRenderer $jsUrl,
        \M2E\Otto\Block\Adminhtml\Magento\Renderer\JsTranslatorRenderer $jsTranslator
    ): void;

    abstract public function renderHtml(string $searchBtnHtml, string $resetBtnHtml): string;

    public function addCss(\M2E\Otto\Block\Adminhtml\Magento\Renderer\CssRenderer $css): void
    {
        $css->add(
            <<<CSS
        .advanced-filter-btn-wrap > button {
            font-size: 13px;
            margin-top: 10px;
        }

        .advanced-filter-select {
            width: 30%;
            margin-bottom: 15px;
        }

        .advanced-filter-select-container .admin__field-label {
            font-weight: 600;
            margin-right: 15px;
        }

        .advanced-filter-popup > span {
            font-weight: 600;
            margin-right: 15px;
        }

        .advanced-filter-name {
            width: 50%;
            margin-top: 22px;
            margin-left: 8px;
        }
CSS
        );
    }

    protected function wrapFilterHtmlBtn(string $htmlBtn): string
    {
        return sprintf('<div class="advanced-filter-btn-wrap">%s</div>', $htmlBtn);
    }
}
