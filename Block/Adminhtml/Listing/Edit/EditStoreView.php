<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Listing\Edit;

class EditStoreView extends \M2E\Otto\Block\Adminhtml\Magento\Form\AbstractContainer
{
    private \M2E\Otto\Model\Listing $listing;

    public function __construct(
        \M2E\Otto\Block\Adminhtml\Magento\Context\Widget $context,
        \M2E\Otto\Model\Listing $listing,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->listing = $listing;
    }

    public function _construct()
    {
        parent::_construct();

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
    }

    protected function _prepareLayout()
    {
        $this->addChild(
            'form',
            \M2E\Otto\Block\Adminhtml\Listing\Edit\StoreView\Form::class,
            ['listing' => $this->listing]
        );

        return parent::_prepareLayout();
    }
}