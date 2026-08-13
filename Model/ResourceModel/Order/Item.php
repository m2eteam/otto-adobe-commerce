<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Order;

class Item extends \M2E\Otto\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_ORDER_ID = 'order_id';
    public const COLUMN_PRODUCT_ID = 'product_id';
    public const COLUMN_PRODUCT_DETAILS = 'product_details';
    public const COLUMN_QTY_RESERVED = 'qty_reserved';
    public const COLUMN_ADDITIONAL_DATA = 'additional_data';
    public const COLUMN_OTTO_ITEM_ID = 'otto_item_id';
    public const COLUMN_OTTO_PRODUCT_SKU = 'otto_product_sku';
    public const COLUMN_ARTICLE_NUMBER = 'article_number';
    public const COLUMN_TITLE = 'title';
    public const COLUMN_EAN = 'ean';
    public const COLUMN_QTY_PURCHASED = 'qty_purchased';
    public const COLUMN_SALE_PRICE = 'sale_price';
    public const COLUMN_PLATFORM_DISCOUNT = 'platform_discount';
    public const COLUMN_TAX_DETAILS = 'tax_details';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_TRACKING_DETAILS = 'tracking_details';
    public const COLUMN_IS_CANCELLATION_IN_PROGRESS = 'is_cancellation_in_progress';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct()
    {
        $this->_init(
            \M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_ORDER_ITEM,
            self::COLUMN_ID
        );
    }
}
