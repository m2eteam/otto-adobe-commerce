<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel;

class Account extends \M2E\Otto\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_TITLE = 'title';
    public const COLUMN_SERVER_HASH = 'server_hash';
    public const COLUMN_INSTALLATION_ID = 'installation_id';
    public const COLUMN_MODE = 'mode';
    public const COLUMN_MAGENTO_ORDERS_SETTINGS = 'magento_orders_settings';
    public const COLUMN_CREATE_MAGENTO_INVOICE = 'create_magento_invoice';
    public const COLUMN_CREATE_MAGENTO_SHIPMENT = 'create_magento_shipment';
    public const COLUMN_OTHER_LISTINGS_SYNCHRONIZATION = 'other_listings_synchronization';
    public const COLUMN_OTHER_LISTINGS_MAPPING_MODE = 'other_listings_mapping_mode';
    public const COLUMN_OTHER_LISTINGS_MAPPING_SETTINGS = 'other_listings_mapping_settings';
    public const COLUMN_OTHER_LISTINGS_RELATED_STORE_ID = 'other_listings_related_store_id';
    public const COLUMN_ORDER_LAST_SYNC = 'orders_last_synchronization';
    public const COLUMN_INVENTORY_LAST_SYNC = 'inventory_last_synchronization';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct(): void
    {
        $this->_init(
            \M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_ACCOUNT,
            self::COLUMN_ID
        );
    }
}