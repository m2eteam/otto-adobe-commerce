<?php

namespace M2E\Otto\Block\Adminhtml\Magento\Renderer;

/**
 * Class \M2E\Otto\Block\Adminhtml\Magento\Renderer\JsUrlRenderer
 */
class JsUrlRenderer extends AbstractRenderer
{
    protected $jsUrls = [];

    public function add($url, $alias = null)
    {
        if ($alias === null) {
            $alias = $url;
        }
        $this->jsUrls[$alias] = $url;

        return $this;
    }

    public function addUrls(array $urls)
    {
        $this->jsUrls = array_merge($this->jsUrls, $urls);

        return $this;
    }

    public function render()
    {
        if (empty($this->jsUrls)) {
            return '';
        }

        $urls = \M2E\Otto\Helper\Json::encode($this->jsUrls);

        return "Otto.url.add({$urls});";
    }
}
