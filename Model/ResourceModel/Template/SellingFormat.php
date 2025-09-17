<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Template;

class SellingFormat extends \M2E\Otto\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_TITLE = 'title';
    public const COLUMN_IS_CUSTOM_TEMPLATE = 'is_custom_template';
    public const COLUMN_QTY_MODE = 'qty_mode';
    public const COLUMN_QTY_CUSTOM_VALUE = 'qty_custom_value';
    public const COLUMN_QTY_CUSTOM_ATTRIBUTE = 'qty_custom_attribute';
    public const COLUMN_QTY_PERCENTAGE = 'qty_percentage';
    public const COLUMN_QTY_MODIFICATION_MODE = 'qty_modification_mode';
    public const COLUMN_QTY_MIN_POSTED_VALUE = 'qty_min_posted_value';
    public const COLUMN_QTY_MAX_POSTED_VALUE = 'qty_max_posted_value';
    public const COLUMN_FIXED_PRICE_MODE = 'fixed_price_mode';
    public const COLUMN_FIXED_PRICE_MODIFIER = 'fixed_price_modifier';
    public const COLUMN_FIXED_PRICE_CUSTOM_ATTRIBUTE = 'fixed_price_custom_attribute';
    public const COLUMN_SALE_PRICE_MODE = 'sale_price_mode';
    public const COLUMN_SALE_PRICE_ATTRIBUTE = 'sale_price_attribute';
    public const COLUMN_SALE_PRICE_START_DATE_MODE = 'sale_price_start_date_mode';
    public const COLUMN_SALE_PRICE_START_DATE_VALUE = 'sale_price_start_date_value';
    public const COLUMN_SALE_PRICE_END_DATE_MODE = 'sale_price_end_date_mode';
    public const COLUMN_SALE_PRICE_END_DATE_VALUE = 'sale_price_end_date_value';
    public const COLUMN_MSRP_MODE = 'msrp_mode';
    public const COLUMN_MSRP_ATTRIBUTE = 'msrp_attribute';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct(): void
    {
        $this->_init(
            \M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_TEMPLATE_SELLING_FORMAT,
            self::COLUMN_ID
        );
    }
}
