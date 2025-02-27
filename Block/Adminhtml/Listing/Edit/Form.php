<?php

namespace M2E\Otto\Block\Adminhtml\Listing\Edit;

use M2E\Otto\Block\Adminhtml\Magento\Form\AbstractForm;

class Form extends AbstractForm
{
    private \M2E\Otto\Helper\Data\GlobalData $globalDataHelper;

    public function __construct(
        \M2E\Otto\Helper\Data\GlobalData $globalDataHelper,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->globalDataHelper = $globalDataHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $listing = $this->globalDataHelper->getValue('edit_listing');

        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => 'javascript:void(0)',
                    'method' => 'post',
                ],
            ]
        );

        $form->addField(
            'id',
            'hidden',
            [
                'name' => 'id',
            ]
        );

        $fieldset = $form->addFieldset(
            'edit_listing_fieldset',
            []
        );

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'class' => 'validate-no-empty Otto-listing-title',
                'label' => __('Title'),
                'field_extra_attributes' => 'style="margin-bottom: 0;"',
            ]
        );

        if ($listing) {
            $form->addValues($listing->getData());
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
