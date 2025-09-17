<?php

namespace M2E\Otto\Model\ResourceModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link;

class Product extends \M2E\Otto\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_LISTING_ID = 'listing_id';
    public const COLUMN_MAGENTO_PRODUCT_ID = 'magento_product_id';
    public const COLUMN_OTTO_PRODUCT_SKU = 'otto_product_sku';
    public const COLUMN_ONLINE_SKU = 'online_sku';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_STATUS_CHANGE_DATE = 'status_change_date';
    public const COLUMN_IS_INCOMPLETE = 'is_incomplete';
    public const COLUMN_STATUS_CHANGER = 'status_changer';
    public const COLUMN_ONLINE_EAN = 'online_ean';
    public const COLUMN_ONLINE_PRODUCT_REFERENCE = 'online_product_reference';
    public const COLUMN_ONLINE_TITLE = 'online_title';
    public const COLUMN_ONLINE_DESCRIPTION = 'online_description';
    public const COLUMN_ONLINE_BRAND_ID = 'online_brand_id';
    public const COLUMN_ONLINE_BRAND_NAME = 'online_brand_name';
    public const COLUMN_ONLINE_MPN = 'online_mpn';
    public const COLUMN_ONLINE_MANUFACTURER = 'online_manufacturer';
    public const COLUMN_ONLINE_CATEGORY = 'online_category';
    public const COLUMN_ONLINE_CATEGORY_ATTRIBUTES_DATA = 'online_category_attributes_data';
    public const COLUMN_ONLINE_IMAGES_DATA = 'online_images_data';
    public const COLUMN_ONLINE_PRICE = 'online_price';
    public const COLUMN_ONLINE_SALE_PRICE = 'online_sale_price';
    public const COLUMN_ONLINE_MSRP = 'online_msrp';
    public const COLUMN_ONLINE_SALE_PRICE_START_DATE = 'online_sale_price_start_date';
    public const COLUMN_ONLINE_SALE_PRICE_END_DATE = 'online_sale_price_end_date';
    public const COLUMN_ONLINE_QTY = 'online_qty';
    public const COLUMN_ONLINE_VAT = 'online_vat';
    public const COLUMN_ONLINE_DELIVERY_DATA = 'online_delivery_data';
    public const COLUMN_ONLINE_SHIPPING_PROFILE_ID = 'online_shipping_profile_id';
    public const COLUMN_ONLINE_DELIVERY_TYPE = 'online_delivery_type';
    public const COLUMN_PRODUCT_MOIN = 'product_moin';
    public const COLUMN_OTTO_PRODUCT_URL = 'otto_product_url';
    public const COLUMN_TEMPLATE_CATEGORY_ID = 'template_category_id';
    public const COLUMN_LAST_BLOCKING_ERROR_DATE = 'last_blocking_error_date';
    public const COLUMN_ADDITIONAL_DATA = 'additional_data';
    public const COLUMN_MARKETPLACE_ERRORS = 'marketplace_errors';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    private \Magento\Framework\EntityManager\MetadataPool $metadataPool;
    private \M2E\Otto\Helper\Module\Database\Structure $dbStructureHelper;

    public function __construct(
        \M2E\Otto\Helper\Module\Database\Structure $dbStructureHelper,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        $connectionName = null
    ) {
        parent::__construct(
            $context,
            $connectionName
        );
        $this->metadataPool = $metadataPool;
        $this->dbStructureHelper = $dbStructureHelper;
    }

    public function _construct(): void
    {
        $this->_init(
            \M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_PRODUCT,
            self::COLUMN_ID
        );
    }

    public function getProductIds(array $listingProductIds): array
    {
        $select = $this->getConnection()
                       ->select()
                       ->from(['lp' => $this->getMainTable()])
                       ->reset(\Magento\Framework\DB\Select::COLUMNS)
                       ->columns(['product_id'])
                       ->where('id IN (?)', $listingProductIds);

        return $select->query()->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function getParentEntityIdsByChild($childId)
    {
        $select = $this->getConnection()
                       ->select()
                       ->from([
                           'l' => $this->dbStructureHelper->getTableNameWithPrefix('catalog_product_link'),
                       ], [])
                       ->join(
                           [
                               'e' => $this->dbStructureHelper->getTableNameWithPrefix('catalog_product_entity'),
                           ],
                           'e.' .
                           $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField(
                           ) . ' = l.product_id',
                           ['e.entity_id']
                       )
                       ->where('l.linked_product_id = ?', $childId)
                       ->where(
                           'link_type_id = ?',
                           Link::LINK_TYPE_GROUPED
                       );

        return $this->getConnection()->fetchCol($select);
    }

    public function getTemplateCategoryIds(array $listingProductIds, $columnName, $returnNull = false)
    {
        $select = $this->getConnection()
                       ->select()
                       ->from(['product' => $this->getMainTable()])
                       ->reset(\Magento\Framework\DB\Select::COLUMNS)
                       ->columns([$columnName])
                       ->where('id IN (?)', $listingProductIds);

        !$returnNull && $select->where("{$columnName} IS NOT NULL");

        foreach ($select->query()->fetchAll() as $row) {
            $id = $row[$columnName] !== null ? (int)$row[$columnName] : null;
            if (!$returnNull) {
                continue;
            }

            $ids[$id] = $id;
        }

        return array_values($ids);
    }
}
