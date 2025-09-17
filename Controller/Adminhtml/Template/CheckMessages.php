<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Template;

class CheckMessages extends \M2E\Otto\Controller\Adminhtml\AbstractBase
{
    private \Magento\Store\Model\StoreManagerInterface $storeManager;
    private \M2E\Otto\Model\Template\SellingFormat\Repository $sellingRepository;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \M2E\Otto\Model\Template\SellingFormat\Repository $sellingRepository,
        $context = null
    ) {
        parent::__construct($context);
        $this->sellingRepository = $sellingRepository;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        $templateId = (int)$this->getRequest()->getParam('id');
        $nick = $this->getRequest()->getParam('nick');

        if ($nick == \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_SELLING_FORMAT) {
            $this->sellingRepository->get($templateId);
        }

        $store = $this->storeManager->getStore((int)$this->getRequest()->getParam('store_id'));

        /** @var \M2E\Otto\Block\Adminhtml\Template\SellingFormat\Messages $messagesBlock */
        $messagesBlock = $this
            ->getLayout()
            ->createBlock(
                \M2E\Otto\Block\Adminhtml\Template\SellingFormat\Messages::class,
                '',
                [
                    'currencyCode' => \M2E\Otto\Model\Currency::CURRENCY_EUR,
                    'store' => $store,
                ]
            );

        $this->setJsonContent(['messages' => $messagesBlock->getMessagesHtml()]);

        return $this->getResult();
    }
}
