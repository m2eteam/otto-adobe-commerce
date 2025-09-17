<?php

namespace M2E\Otto\Controller\Adminhtml\Otto\Template;

use M2E\Otto\Controller\Adminhtml\Otto\AbstractTemplate;

class GetTemplateHtml extends AbstractTemplate
{
    private \M2E\Otto\Helper\Component\Otto\Template\Switcher\DataLoader $templateSwitcherDataLoader;

    public function __construct(
        \M2E\Otto\Helper\Component\Otto\Template\Switcher\DataLoader $templateSwitcherDataLoader,
        \M2E\Otto\Model\Otto\Template\Manager $templateManager
    ) {
        parent::__construct($templateManager);

        $this->templateSwitcherDataLoader = $templateSwitcherDataLoader;
    }

    public function execute()
    {
        try {
            $dataLoader = $this->templateSwitcherDataLoader;
            $dataLoader->load($this->getRequest());

            $templateNick = $this->getRequest()->getParam('nick');
            $templateDataForce = (bool)$this->getRequest()->getParam('data_force', false);

            /** @var \M2E\Otto\Block\Adminhtml\Otto\Listing\Template\Switcher $switcherBlock */
            $switcherBlock = $this
                ->getLayout()
                ->createBlock(\M2E\Otto\Block\Adminhtml\Otto\Listing\Template\Switcher::class);

            $switcherBlock->setData(['template_nick' => $templateNick]);

            $this->setAjaxContent($switcherBlock->getFormDataBlockHtml($templateDataForce));
        } catch (\Exception $e) {
            $this->setJsonContent(['error' => $e->getMessage()]);
        }

        return $this->getResult();
    }
}
