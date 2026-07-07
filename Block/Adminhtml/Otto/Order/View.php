<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Order;

use M2E\Otto\Block\Adminhtml\Magento\Form\AbstractContainer;

class View extends AbstractContainer
{
    private \M2E\Core\Helper\Url $urlHelper;
    private \M2E\Otto\Model\Order $order;

    public function __construct(
        \M2E\Otto\Model\Order $order,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Widget $context,
        \M2E\Core\Helper\Url $urlHelper,
        array $data = []
    ) {
        $this->urlHelper = $urlHelper;
        $this->order = $order;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('ottoOrderView');
        $this->_controller = 'adminhtml_otto_order';
        $this->_mode = 'view';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->addBackButton();
        $this->addReservationButtons();
        $this->addCreateMagentoOrderButton();
    }

    protected function _prepareLayout()
    {
        $formBlock = $this->getLayout()->createBlock(
            \M2E\Otto\Block\Adminhtml\Otto\Order\View\Form::class,
            '',
            ['order' => $this->order]
        );

        $this->setChild('form', $formBlock);

        return parent::_prepareLayout();
    }

    private function addBackButton(): void
    {
        $this->addButton('back', [
            'label' => __('Back'),
            'onclick' => sprintf(
                "CommonObj.backClick('%s')",
                $this->urlHelper->getBackUrl('*/otto_order/index')
            ),
            'class' => 'back',
        ]);
    }

    private function addReservationButtons(): void
    {
        if ($this->order->getReserve()->isPlaced()) {
            $this->addButton('reservation_cancel', [
                'label' => __('Cancel QTY Reserve'),
                'onclick' => sprintf(
                    "confirmSetLocation(Otto.translator.translate('%s'), '%s');",
                    __('Are you sure?'),
                    $this->getUrl('*/order/reservationCancel', ['ids' => $this->order->getId()])
                ),
                'class' => 'primary',
            ]);
        }

        if ($this->order->isReservable()) {
            $this->addButton('reservation_place', [
                'label' => __('Reserve QTY'),
                'onclick' => sprintf(
                    "confirmSetLocation('%s', '%s');",
                    __('Are you sure?'),
                    $this->getUrl('*/order/reservationPlace', ['ids' => $this->order->getId()])
                ),
                'class' => 'primary',
            ]);
        }
    }

    private function addCreateMagentoOrderButton(): void
    {
        if (!$this->order->canCreateMagentoOrder()) {
            return;
        }

        $this->addButton('order', [
            'label' => __('Create Magento Order'),
            'onclick' => sprintf(
                "setLocation('%s');",
                $this->getUrl('*/*/createMagentoOrder', ['id' => $this->order->getId()])
            ),
            'class' => 'primary',
        ]);
    }
}
