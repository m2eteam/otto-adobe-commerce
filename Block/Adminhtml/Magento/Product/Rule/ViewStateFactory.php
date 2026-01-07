<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Magento\Product\Rule;

class ViewStateFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(string $viewKey): ViewState
    {
        return $this->objectManager->create(ViewState::class, [
            'viewKey' => $viewKey,
        ]);
    }
}
