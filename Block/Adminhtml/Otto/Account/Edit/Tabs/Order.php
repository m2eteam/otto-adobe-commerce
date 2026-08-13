<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Account\Edit\Tabs;

use M2E\Otto\Model\Account\Settings\Order as OrderSettings;
use M2E\Otto\Block\Adminhtml\Magento\Form\AbstractForm;
use M2E\Otto\Model\Account;
use Magento\Framework\Message\MessageInterface;

class Order extends AbstractForm
{
    private \Magento\Sales\Model\Order\Config $orderConfig;
    private \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroupCollectionFactory;
    private \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory $taxClassCollectionFactory;
    private \M2E\Core\Helper\Magento\Store\Website $storeWebsite;
    private \M2E\Core\Helper\Magento\Store $storeHelper;
    private ?Account $account;

    public function __construct(
        \Magento\Sales\Model\Order\Config $orderConfig,
        \M2E\Core\Helper\Magento\Store $storeHelper,
        \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory $taxClassCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroupCollectionFactory,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\Core\Helper\Magento\Store\Website $storeWebsite,
        ?\M2E\Otto\Model\Account $account = null,
        array $data = []
    ) {
        $this->customerGroupCollectionFactory = $customerGroupCollectionFactory;
        $this->taxClassCollectionFactory = $taxClassCollectionFactory;
        $this->storeWebsite = $storeWebsite;
        $this->storeHelper = $storeHelper;
        $this->account = $account;
        $this->orderConfig = $orderConfig;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $orderSettings = new Account\Settings\Order();
        if ($this->account !== null) {
            $orderSettings = $this->account->getOrdersSettings();
        }

        $form = $this->_formFactory->create();

        $form->addField(
            'otto_accounts_orders',
            self::HELP_BLOCK,
            [
                'content' => __(
                    '<p>Here you can define how %extension_title will manage orders imported from %channel_title marketplace in Magento sales:</p><br/>
<p><strong>Rules for Magento Order Creation:</strong> Whether the Item was listed via %extension_title or through another software,
 you can confirm if it should be created in Magento.</p><br/>
<p><strong>Quantity Reservation Feature:</strong> In case a Magento order cannot be created immediately,
 the Reserve Quantity feature will hold the stock for the order. This ensures inventory availability
  and prevents overselling.</p><br/>
<p><strong>Order Number Customization:</strong> You may choose to customize Magento Order number to match your store setup.</p>',
                    [
                        'channel_title' => \M2E\Otto\Helper\Module::getChannelTitle(),
                        'extension_title' => \M2E\Otto\Helper\Module::getExtensionTitle(),
                    ]
                ),
            ]
        );

        //region Product Is Listed By M2E
        $fieldset = $form->addFieldset(
            'listed_by_m2e',
            [
                'legend' => __(
                    'Product Is Listed By %extension_title',
                    [
                        'extension_title' => \M2E\Otto\Helper\Module::getExtensionTitle(),
                    ]
                ),
                'collapsable' => false,
            ]
        );

        $fieldset->addField(
            'magento_orders_listings_mode',
            'select',
            [
                'name' => 'magento_orders_settings[listing][mode]',
                'label' => __('Create Order in Magento'),
                'values' => [
                    1 => __('Yes'),
                    0 => __('No'),
                ],
                'value' => (int)$orderSettings->isListingEnabled(),
                'tooltip' => __(
                    'Choose whether a Magento Order should be created if an %channel_title Order is received for
    an %channel_title Item Listed using %extension_title.',
                    [
                        'channel_title' => \M2E\Otto\Helper\Module::getChannelTitle(),
                        'extension_title' => \M2E\Otto\Helper\Module::getExtensionTitle(),
                    ]
                ),

            ]
        );

        $fieldset->addField(
            'magento_orders_listings_store_mode',
            'select',
            [
                'container_id' => 'magento_orders_listings_store_mode_container',
                'name' => 'magento_orders_settings[listing][store_mode]',
                'label' => __('Magento Store View Source'),
                'values' => [
                    \M2E\Otto\Model\Account\Settings\Order::LISTINGS_STORE_MODE_DEFAULT => __('Use Store View from Listing'),
                    \M2E\Otto\Model\Account\Settings\Order::LISTINGS_STORE_MODE_CUSTOM => __('Choose Store View Manually'),
                ],
                'value' => $orderSettings->getListingStoreMode(),
                'tooltip' => __(
                    'Choose to specify the Magento Store View here or to keep the Magento
                    Store View used in the %extension_title Listing.',
                    [
                        'extension_title' => \M2E\Otto\Helper\Module::getExtensionTitle(),
                    ]
                ),
            ]
        );

        $fieldset->addField(
            'magento_orders_listings_store_id',
            self::STORE_SWITCHER,
            [
                'container_id' => 'magento_orders_listings_store_id_container',
                'name' => 'magento_orders_settings[listing][store_id]',
                'label' => __('Magento Store View'),
                'required' => true,
                'value' => $orderSettings->getListingStoreIdForCustomMode() ?: $this->storeHelper->getDefaultStoreId(),
                'has_empty_option' => true,
                'has_default_option' => false,
                'tooltip' => __('The Magento Store View that Orders will be placed in.'),
            ]
        );
        //endregion

        //region Product Is Listed By Any Other Software
        $fieldset = $form->addFieldset(
            'listed_by_other',
            [
                'legend' => __('Product Is Listed By Any Other Software'),
                'collapsable' => false,
            ]
        );

        $fieldset->addField(
            'magento_orders_listings_other_mode',
            'select',
            [
                'name' => 'magento_orders_settings[listing_other][mode]',
                'label' => __('Create Order in Magento'),
                'values' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
                'value' => (int)$orderSettings->isUnmanagedListingEnabled(),
                'tooltip' => __(
                    'Choose whether a Magento Order should be created if an %channel_title Order is received
for an item that does <b>not</b> belong to the %extension_title Listing.',
                    [
                        'channel_title' => \M2E\Otto\Helper\Module::getChannelTitle(),
                        'extension_title' => \M2E\Otto\Helper\Module::getExtensionTitle(),
                    ]
                ),
            ]
        );

        $fieldset->addField(
            'magento_orders_listings_other_store_id',
            self::STORE_SWITCHER,
            [
                'container_id' => 'magento_orders_listings_other_store_id_container',
                'name' => 'magento_orders_settings[listing_other][store_id]',
                'label' => __('Magento Store View'),
                'required' => true,
                'value' => $orderSettings->getUnmanagedListingStoreId() ?: $this->storeHelper->getDefaultStoreId(),
                'has_empty_option' => true,
                'has_default_option' => false,
                'tooltip' => __('The Magento Store View that Orders will be placed in.'),
            ]
        );
        //endregion

        //region Magento Order Number
        $fieldset = $form->addFieldset(
            'magento_block_otto_accounts_magento_orders_number',
            [
                'legend' => __('Magento Order Number'),
                'collapsable' => true,
            ]
        );

        $fieldset->addField(
            'magento_orders_number_source',
            'select',
            [
                'name' => 'magento_orders_settings[number][source]',
                'label' => __('Source'),
                'values' => [
                    \M2E\Otto\Model\Account\Settings\Order::NUMBER_SOURCE_MAGENTO => __('Magento'),
                    \M2E\Otto\Model\Account\Settings\Order::NUMBER_SOURCE_CHANNEL => __(\M2E\Otto\Helper\Module::getChannelTitle()),
                ],
                'value' => $orderSettings->getMagentoOrderNumberSource(),
                'tooltip' => __(
                    'If source is set to Magento, Magento Order numbers are created basing ' .
                    'on your Magento Settings. If source is set to %channel_title, Magento Order numbers are the same ' .
                    'as %channel_title Order numbers.',
                    [
                        'channel_title' => \M2E\Otto\Helper\Module::getChannelTitle(),
                    ],
                ),
            ]
        );

        $fieldset->addField(
            'magento_orders_number_prefix_prefix',
            'text',
            [
                'container_id' => 'magento_orders_number_prefix_container',
                'name' => 'magento_orders_settings[number][prefix][prefix]',
                'label' => __('General Prefix'),
                'value' => $orderSettings->getMagentoOrdersNumberRegularPrefix(),
                'maxlength' => 10,
            ]
        );

        $fieldset->addField(
            'order_number_example',
            'label',
            [
                'label' => '',
                'note' => __('e.g.') . ' <span id="order_number_example_container"></span>',
            ]
        );
        //endregion

        //region Shipping information
        $shipByDateFieldset = $form->addFieldset(
            'magento_block_otto_accounts_magento_orders_shipping_information',
            [
                'legend' => __('Shipping information'),
                'collapsable' => true,
            ]
        );

        $shipByDateFieldset->addField(
            'magento_orders_ship_by_date_settings',
            'select',
            [
                'name' => 'magento_orders_settings[shipping_information][ship_by_date]',
                'label' => __('Import Ship by date to Magento order'),
                'values' => [
                    1 => __('Yes'),
                    0 => __('No'),
                ],
                'value' => (int)$orderSettings->isImportShipByDate(),
            ]
        );

        $shipByDateFieldset->addField(
            'magento_orders_order_validation_shipping_address_region_override',
            'select',
            [
                'name' => 'magento_orders_settings[shipping_information][shipping_address_region_override]',
                'label' => __('Override invalid Region/State required value'),
                'values' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
                'value' => (int)$orderSettings->isRegionOverrideRequired(),
                'tooltip' => __(
                    'When enabled, the invalid Region/State value will be replaced with an alternative one to create
                     an order in Magento.'
                ),
            ]
        );
        //endregion

        //region Customer Settings
        $fieldset = $form->addFieldset(
            'magento_block_otto_accounts_magento_orders_customer',
            [
                'legend' => __('Customer Settings'),
                'collapsable' => true,
            ]
        );

        $fieldset->addField(
            'magento_orders_customer_mode',
            'select',
            [
                'name' => 'magento_orders_settings[customer][mode]',
                'label' => __('Customer'),
                'values' => [
                    \M2E\Otto\Model\Account\Settings\Order::CUSTOMER_MODE_GUEST => __('Guest Account'),
                    \M2E\Otto\Model\Account\Settings\Order::CUSTOMER_MODE_PREDEFINED => __('Predefined Customer'),
                    \M2E\Otto\Model\Account\Settings\Order::CUSTOMER_MODE_NEW => __('Create New'),
                ],
                'value' => $orderSettings->getCustomerMode(),
                'tooltip' => __(
                    '<b>Guest Account:</b> Magento Guest Checkout Option must be enabled to use this Option.
                    Use the default Guest Account. Do not create a Customer Account.<br/><br/>
                    <b>Predefined Customer:</b> Use a specific Customer for all Orders.
                    You should specify the Magento Customer ID to use.<br/><br/>
                    <b>Create New:</b> Create a new Customer in Magento for the Order.
                    If an existing Magento Customer has the same email address as the email address used for the
                    %channel_title Order, the Order will be assigned to that Customer instead.',
                    ['channel_title' => \M2E\Otto\Helper\Module::getChannelTitle()]
                ),
            ]
        );

        $fieldset->addField(
            'magento_orders_customer_id',
            'text',
            [
                'container_id' => 'magento_orders_customer_id_container',
                'class' => 'validate-digits Otto-account-customer-id',
                'name' => 'magento_orders_settings[customer][id]',
                'label' => __('Customer ID'),
                'value' => $orderSettings->getCustomerPredefinedId(),
                'required' => true,
            ]
        );
        $values = [];
        foreach ($this->storeWebsite->getWebsites(true) as $website) {
            $values[$website['website_id']] = $website['name'];
        }

        $fieldset->addField(
            'magento_orders_customer_new_website_id',
            'select',
            [
                'container_id' => 'magento_orders_customer_new_website_id_container',
                'name' => 'magento_orders_settings[customer][website_id]',
                'label' => __('Associate to Website'),
                'values' => $values,
                'value' => $orderSettings->getCustomerNewWebsiteId(),
                'required' => true,
            ]
        );

        $temp = $this->customerGroupCollectionFactory->create()->toArray();

        $values = [];
        foreach ($temp['items'] as $group) {
            $values[$group['customer_group_id']] = $group['customer_group_code'];
        }

        $fieldset->addField(
            'magento_orders_customer_new_group_id',
            'select',
            [
                'container_id' => 'magento_orders_customer_new_group_id_container',
                'name' => 'magento_orders_settings[customer][group_id]',
                'label' => __('Customer Group'),
                'values' => $values,
                'value' => $orderSettings->getCustomerNewGroupId(),
                'required' => true,
            ]
        );

        $value = [];
        if ($orderSettings->isCustomerNewNotifyWhenOrderCreated()) {
            $value[] = 'order_created';
        }
        if ($orderSettings->isCustomerNewNotifyWhenInvoiceCreated()) {
            $value[] = 'invoice_created';
        }

        $fieldset->addField(
            'magento_orders_customer_new_notifications',
            'multiselect',
            [
                'container_id' => 'magento_orders_customer_new_notifications_container',
                'name' => 'magento_orders_settings[customer][notifications][]',
                'label' => __('Send Emails When The Following Is Created'),
                'values' => [
                    ['label' => __('Magento Order'), 'value' => 'order_created'],
                    ['label' => __('Invoice'), 'value' => 'invoice_created'],
                ],
                'value' => $value,
                'tooltip' => __(
                    '<p>Necessary emails will be sent according to Magento Settings in
                    Stores > Configuration > Sales > Sales Emails.</p>
                    <p>Hold Ctrl Button to choose more than one Option.</p>'
                ),
            ]
        );
        //endregion

        //region Order Creation Rules
        $fieldset = $form->addFieldset(
            'magento_block_otto_accounts_magento_orders_rules',
            [
                'legend' => __('Order Creation Rules'),
                'collapsable' => true,
            ]
        );

        $fieldset->addField(
            'magento_orders_creation_mode_immediately_warning',
            self::MESSAGES,
            [
                'messages' => [
                    [
                        'type' => MessageInterface::TYPE_WARNING,
                        'content' => __(
                            'Please note that Immediate Magento order creation sets certain limits to the update of
                            the later order changes. If the shipping or tax details are modified after checkout is
                            completed, these changes will not be reflected in Magento order.'
                        ),
                    ],
                ],
                'style' => 'display: none',
            ]
        );

        $values = [];
        for ($day = 1; $day <= 14; $day++) {
            if ($day === 1) {
                $values[$day] = __('For %number day', ['number' => $day]);
            } else {
                $values[$day] = __('For %number days', ['number' => $day]);
            }
        }

        $fieldset->addField(
            'magento_orders_qty_reservation_days',
            'select',
            [
                'container_id' => 'magento_orders_qty_reservation_days_container',
                'name' => 'magento_orders_settings[qty_reservation][days]',
                'label' => __('Reserve Quantity'),
                'values' => $values,
                'value' => $orderSettings->getQtyReservationDays(),
                'tooltip' => __(
                    'Choose for how long %extension_title should reserve Magento Product quantity per %channel_title Order until
    Magento Order is created.',
                    [
                        'channel_title' => \M2E\Otto\Helper\Module::getChannelTitle(),
                        'extension_title' => \M2E\Otto\Helper\Module::getExtensionTitle(),
                    ]
                ),
            ]
        );
        //endregion

        //region Refund & Cancellation
        $fieldset = $form->addFieldset(
            'magento_block_otto_accounts_magento_orders_cancellation',
            [
                'legend' => $this->__('Refund & Cancellation'),
                'collapsable' => true,
            ]
        );

        $fieldset->addField(
            'cancel_channel_order_if_creditmemo_created',
            'select',
            [
                'name' => 'magento_orders_settings[cancel_channel_order_if_creditmemo_created][mode]',
                'label' => __('Cancel Otto Order'),
                'values' => [
                    OrderSettings::CANCEL_CHANNEL_ORDER_IF_CREDITMEMO_CREATED_YES => __('Yes'),
                    OrderSettings::CANCEL_CHANNEL_ORDER_IF_CREDITMEMO_CREATED_NO => __('No'),
                ],
                'value' => $orderSettings->getCancelChannelOrderIfCreditmemoCreatedMode(),
                'tooltip' => __('Enable this option to automatically cancel Otto orders and update their ' .
                    'status on the channel after a Credit Memo is created in Magento.'),
            ]
        );

        $fieldset->addField(
            'magento_orders_create_creditmemo_if_order_cancelled',
            'select',
            [
                'name' => 'magento_orders_settings[create_creditmemo_if_order_cancelled][mode]',
                'label' => __('Automatically create Credit Memo when Order is cancelled'),
                'values' => [
                    OrderSettings::CREATE_CREDIT_MEMO_IF_ORDER_CANCELLED_YES => __('Yes'),
                    OrderSettings::CREATE_CREDIT_MEMO_IF_ORDER_CANCELLED_NO => __('No'),
                ],
                'value' => $orderSettings->getCreateCreditMemoIfOrderCancelledMode(),
            ]
        );
        //endregion

        //region Order Tax Settings
        $fieldset = $form->addFieldset(
            'magento_block_otto_accounts_magento_orders_tax',
            [
                'legend' => __('Order Tax Settings'),
                'collapsable' => true,
            ]
        );

        $fieldset->addField(
            'magento_orders_tax_mode',
            'select',
            [
                'name' => 'magento_orders_settings[tax][mode]',
                'label' => __('Tax Source'),
                'disabled' => true,
                'values' => [
                    \M2E\Otto\Model\Account\Settings\Order::TAX_MODE_MAGENTO => __('Magento')
                ],
                'value' => $orderSettings->getTaxMode(),
            ]
        );
        //endregion

        //region Status Mapping Settings
        $fieldset = $form->addFieldset(
            'magento_block_otto_accounts_magento_orders_status_mapping',
            [
                'legend' => __('Order Status Mapping'),
                'collapsable' => true,
            ]
        );

        $fieldset->addField(
            'magento_orders_status_mapping_mode',
            'select',
            [
                'name' => 'magento_orders_settings[order_status_mapping][mode]',
                'label' => __('Status Mapping'),
                'values' => [
                    \M2E\Otto\Model\Account\Settings\Order::ORDERS_STATUS_MAPPING_MODE_DEFAULT => __('Default Order Statuses'),
                    \M2E\Otto\Model\Account\Settings\Order::ORDERS_STATUS_MAPPING_MODE_CUSTOM => __('Custom Order Statuses'),
                ],
                'value' => $orderSettings->getStatusMappingMode(),
                'tooltip' => __(
                    'Configure the mapping between %channel_title and Magento order statuses.
                    Magento order statuses will automatically update according to these settings.',
                    ['channel_title' => \M2E\Otto\Helper\Module::getChannelTitle()]
                ),
            ]
        );

        $statusList = $this->orderConfig->getStatuses();

        $fieldset->addField(
            'magento_orders_status_mapping_processing',
            'select',
            [
                'container_id' => 'magento_orders_status_mapping_processing_container',
                'name' => 'magento_orders_settings[order_status_mapping][processing]',
                'label' => __('Order Status is Unshipped / Partially Shipped'),
                'values' => $statusList,
                'value' => $orderSettings->getStatusMappingForProcessing(),
                'disabled' => $orderSettings->isOrderStatusMappingModeDefault(),
            ]
        );

        $fieldset->addField(
            'magento_orders_status_mapping_shipped',
            'select',
            [
                'container_id' => 'magento_orders_status_mapping_shipped_container',
                'name' => 'magento_orders_settings[order_status_mapping][shipped]',
                'label' => __('Shipping Is Completed'),
                'values' => $statusList,
                'value' => $orderSettings->getStatusMappingForProcessingShipped(),
                'disabled' => $orderSettings->isOrderStatusMappingModeDefault(),
            ]
        );
        //endregion

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
