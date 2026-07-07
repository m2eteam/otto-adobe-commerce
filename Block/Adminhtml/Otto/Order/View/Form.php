<?php

namespace M2E\Otto\Block\Adminhtml\Otto\Order\View;

use M2E\Otto\Block\Adminhtml\Magento\AbstractContainer;

class Form extends AbstractContainer
{
    protected $_template = 'otto/order.phtml';

    public ?string $realMagentoOrderId = null;

    public array $shippingAddress = [];

    public \M2E\Otto\Model\Order $order;

    // ----------------------------------------

    private \Magento\Backend\Model\UrlInterface $urlBuilder;
    private \Magento\Tax\Model\Calculation $taxCalculator;
    private \Magento\Store\Model\StoreManager $storeManager;
    private \M2E\Otto\Block\Adminhtml\Otto\Order\StatusHelper $orderStatusHelper;
    private \M2E\Otto\Model\Currency $currency;

    public function __construct(
        \M2E\Otto\Model\Order $order,
        \Magento\Backend\Model\UrlInterface $urlBuilder,
        \M2E\Otto\Block\Adminhtml\Otto\Order\StatusHelper $orderStatusHelper,
        \Magento\Tax\Model\Calculation $taxCalculator,
        \Magento\Store\Model\StoreManager $storeManager,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Widget $context,
        \M2E\Otto\Model\Currency $currency,
        array $data = []
    ) {
        $this->order = $order;
        $this->urlBuilder = $urlBuilder;
        $this->taxCalculator = $taxCalculator;
        $this->storeManager = $storeManager;
        $this->orderStatusHelper = $orderStatusHelper;
        $this->currency = $currency;

        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('ottoOrderViewForm');
    }

    protected function _beforeToHtml()
    {
        // Magento order data
        // ---------------------------------------
        $this->realMagentoOrderId = null;

        $magentoOrder = $this->order->getMagentoOrder();
        if ($magentoOrder !== null) {
            $this->realMagentoOrderId = (string)$magentoOrder->getRealOrderId();
        }
        // ---------------------------------------

        $data = [
            'class' => 'primary',
            'label' => __('Edit'),
            'onclick' => "OrderEditItemObj.openEditShippingAddressPopup({$this->order->getId()});",
        ];
        $buttonBlock = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Magento\Button::class)
                            ->setData($data);
        $this->setChild('edit_shipping_info', $buttonBlock);

        // ---------------------------------------
        if ($magentoOrder !== null && $magentoOrder->hasShipments() && $this->order->canUpdateShippingStatus()) {
            $url = $this->getUrl('*/order/resubmitShippingInfo', ['id' => $this->order->getId()]);
            $data = [
                'class' => 'primary',
                'label' => __('Resend Shipping Information'),
                'onclick' => 'setLocation(\'' . $url . '\');',
            ];
            $buttonBlock = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Magento\Button::class)
                                ->setData($data);
            $this->setChild('resubmit_shipping_info', $buttonBlock);
        }
        // ---------------------------------------

        // Shipping data
        // ---------------------------------------
        /** @var \M2E\Otto\Model\Otto\Order\ShippingAddress $shippingAddress */
        $shippingAddress = $this->order->getShippingAddress();

        $this->shippingAddress = $shippingAddress->getData();
        $this->shippingAddress['country_name'] = $shippingAddress->getCountryName();
        // ---------------------------------------

        // ---------------------------------------
        $buttonAddNoteBlock = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Magento\Button::class)
                                   ->setData(
                                       [
                                           'label' => __('Add Note'),
                                           'onclick' => "OrderNoteObj.openAddNotePopup({$this->order->getId()})",
                                           'class' => 'order_note_btn',
                                       ]
                                   );

        $shippingAddressBlock = $this
            ->getLayout()
            ->createBlock(\M2E\Otto\Block\Adminhtml\Order\Edit\ShippingAddress::class, '', [
                'order' => $this->order,
            ]);
        $this->setChild('shipping_address', $shippingAddressBlock);

        $orderItemsBlock = $this
            ->getLayout()
            ->createBlock(\M2E\Otto\Block\Adminhtml\Otto\Order\View\Item::class, '', [
                'order' => $this->order,
            ]);
        $this->setChild('item', $orderItemsBlock);

        $this->setChild(
            'item_edit',
            $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Order\Item\Edit::class)
        );
        $this->setChild(
            'log',
            $this->getLayout()->createBlock(
                \M2E\Otto\Block\Adminhtml\Order\View\Log\Grid::class,
                '',
                ['order' => $this->order]
            )
        );
        $this->setChild(
            'order_note_grid',
            $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Order\Note\Grid::class)
        );
        $this->setChild('add_note_button', $buttonAddNoteBlock);

        $this->jsUrl->addUrls([
            'order/getDebugInformation' => $this->getUrl(
                '*/order/getDebugInformation/',
                ['id' => $this->getRequest()->getParam('id')]
            ),
            'getEditShippingAddressForm' => $this->getUrl(
                '*/otto_order_shippingAddress/edit',
                ['id' => $this->getRequest()->getParam('id')]
            ),
            'saveShippingAddress' => $this->getUrl(
                '*/otto_order_shippingAddress/save',
                ['id' => $this->getRequest()->getParam('id')]
            ),
            'otto_account/edit' => $this->getUrl(
                '*/otto_account/edit',
                [
                    'close_on_save' => true,
                ]
            ),
        ]);

        $this->jsPhp->addConstants(
            \M2E\Otto\Helper\Data::getClassConstants(
                \M2E\Otto\Controller\Adminhtml\Order\AssignToMagentoProduct::class
            )
        );

        return parent::_beforeToHtml();
    }

    //########################################

    private function getStore(): ?\Magento\Store\Api\Data\StoreInterface
    {
        if (!$this->order->hasStoreId()) {
            return null;
        }

        try {
            $store = $this->storeManager->getStore($this->order->getStoreId());
        } catch (\Throwable $e) {
            return null;
        }

        return $store;
    }

    public function isCurrencyAllowed(): bool
    {
        $store = $this->getStore();
        if ($store === null) {
            return true;
        }

        return $this->currency->isAllowed($this->order->getCurrency(), $store);
    }

    public function hasCurrencyConversionRate()
    {
        $store = $this->getStore();

        if ($store === null) {
            return true;
        }

        return $this->currency->getConvertRateFromBase($this->order->getCurrency(), $store) != 0;
    }

    //########################################

    public function getSubtotalPrice()
    {
        return $this->order->getSubtotalPrice();
    }

    public function getShippingPrice()
    {
        $shippingPrice = $this->order->getShippingPrice();
        if (!$this->order->isVatTax()) {
            return $shippingPrice;
        }

        $shippingPrice -= $this->taxCalculator->calcTaxAmount(
            $shippingPrice,
            $this->order->getTaxRate(),
            true,
            false
        );

        return $shippingPrice;
    }

    public function getTaxAmount()
    {
        $taxAmount = $this->order->getTaxAmount();
        if (!$this->order->isVatTax()) {
            return $taxAmount;
        }

        $shippingPrice = $this->order->getShippingPrice();
        $shippingTaxAmount = $this->taxCalculator->calcTaxAmount(
            $shippingPrice,
            $this->order->getTaxRate(),
            true,
            false
        );

        return $taxAmount + $shippingTaxAmount;
    }

    public function formatPrice($currencyName, $priceValue)
    {
        return $this->currency->formatPrice($currencyName, $priceValue);
    }

    public function getOrderStatusLabel(): string
    {
        return $this->orderStatusHelper->getStatusLabel($this->order->getOrderStatus());
    }

    public function getOrderStatusColor(): string
    {
        return $this->orderStatusHelper->getStatusColor($this->order->getOrderStatus());
    }

    protected function _toHtml()
    {
        $orderNoteGridId = $this->getChildBlock('order_note_grid')->getId();
        $this->jsTranslator
            ->add('Custom Note', __('Custom Note'));

        $this->js->add(
            <<<JS
    require([
        'Otto/Order/Note'
    ], function(){
        window.OrderNoteObj = new OrderNote('$orderNoteGridId');
    });
JS
        );

        return parent::_toHtml();
    }

    public function getUrlBuilder(): \Magento\Backend\Model\UrlInterface
    {
        return $this->urlBuilder;
    }
}
