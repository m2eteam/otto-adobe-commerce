<?php

declare(strict_types=1);

namespace M2E\Otto\Helper\Module\Database;

use Magento\Catalog\Api\Data\ProductAttributeInterface;

class Tables
{
    public const PREFIX = 'm2e_otto_';

    public const TABLE_NAME_CONFIG = self::PREFIX . 'config';
    public const TABLE_NAME_SETUP = self::PREFIX . 'setup';
    public const TABLE_NAME_WIZARD = self::PREFIX . 'wizard';
    public const TABLE_NAME_REGISTRY = self::PREFIX . 'registry';
    public const TABLE_NAME_ACCOUNT = self::PREFIX . 'account';
    public const TABLE_NAME_LISTING = self::PREFIX . 'listing';
    public const TABLE_NAME_LISTING_LOG = self::PREFIX . 'listing_log';
    public const TABLE_NAME_PRODUCT = self::PREFIX . 'product';
    public const TABLE_NAME_PRODUCT_INSTRUCTION = self::PREFIX . 'product_instruction';
    public const TABLE_NAME_PRODUCT_SCHEDULED_ACTION = self::PREFIX . 'product_scheduled_action';

    public const TABLE_NAME_LISTING_WIZARD = self::PREFIX . 'listing_wizard';
    public const TABLE_NAME_LISTING_WIZARD_STEP = self::PREFIX . 'listing_wizard_step';
    public const TABLE_NAME_LISTING_WIZARD_PRODUCT = self::PREFIX . 'listing_wizard_product';

    public const TABLE_NAME_PRODUCT_LOCK = self::PREFIX . 'product_lock';
    public const TABLE_NAME_LOCK_ITEM = self::PREFIX . 'lock_item';
    public const TABLE_NAME_LOCK_TRANSACTIONAL = self::PREFIX . 'lock_transactional';

    public const TABLE_NAME_PROCESSING = self::PREFIX . 'processing';
    public const TABLE_NAME_PROCESSING_PARTIAL_DATA = self::PREFIX . 'processing_partial_data';
    public const TABLE_NAME_PROCESSING_LOCK = self::PREFIX . 'processing_lock';
    public const TABLE_NAME_STOP_QUEUE = self::PREFIX . 'stop_queue';

    public const TABLE_NAME_SYNCHRONIZATION_LOG = self::PREFIX . 'synchronization_log';
    public const TABLE_NAME_SYSTEM_LOG = self::PREFIX . 'system_log';
    public const TABLE_NAME_OPERATION_HISTORY = self::PREFIX . 'operation_history';

    public const TABLE_NAME_TEMPLATE_SELLING_FORMAT = self::PREFIX . 'template_selling_format';
    public const TABLE_NAME_TEMPLATE_SYNCHRONIZATION = self::PREFIX . 'template_synchronization';
    public const TABLE_NAME_TEMPLATE_DESCRIPTION = self::PREFIX . 'template_description';
    public const TABLE_NAME_TEMPLATE_SHIPPING = self::PREFIX . 'template_shipping';

    public const TABLE_NAME_TAG = self::PREFIX . 'tag';
    public const TABLE_NAME_PRODUCT_TAG_RELATION = self::PREFIX . 'product_tag_relation';

    public const TABLE_NAME_ORDER = self::PREFIX . 'order';
    public const TABLE_NAME_ORDER_ITEM = self::PREFIX . 'order_item';
    public const TABLE_NAME_ORDER_LOG = self::PREFIX . 'order_log';
    public const TABLE_NAME_ORDER_NOTE = self::PREFIX . 'order_note';
    public const TABLE_NAME_ORDER_CHANGE = self::PREFIX . 'order_change';

    public const TABLE_NAME_LISTING_OTHER = self::PREFIX . 'listing_other';
    public const TABLE_NAME_CATEGORY_GROUP_DICTIONARY = self::PREFIX . 'category_group_dictionary';
    public const TABLE_NAME_CATEGORY_DICTIONARY = self::PREFIX . 'category_dictionary';
    public const TABLE_NAME_CATEGORY_GROUP_ATTRIBUTE_DICTIONARY = self::PREFIX . 'category_group_attribute_dictionary';
    public const TABLE_NAME_CATEGORY = self::PREFIX . 'category';
    public const TABLE_NAME_CATEGORY_ATTRIBUTES = self::PREFIX . 'category_attributes';
    public const TABLE_NAME_BRAND = self::PREFIX . 'brand';

    private \Magento\Framework\App\ResourceConnection $resourceConnection;
    private Structure $databaseHelper;
    private \M2E\Otto\Helper\Magento\Staging $stagingHelper;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \M2E\Otto\Helper\Module\Database\Structure $databaseHelper,
        \M2E\Otto\Helper\Magento\Staging $stagingHelper
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->databaseHelper = $databaseHelper;
        $this->stagingHelper = $stagingHelper;
    }

    /**
     * @param string $tableName
     *
     * @return bool
     */
    public function isExists(string $tableName): bool
    {
        return $this->resourceConnection
            ->getConnection()
            ->isTableExists($this->getFullName($tableName));
    }

    /**
     * @param string $tableName
     *
     * @return string
     */
    public function getFullName(string $tableName): string
    {
        if (strpos($tableName, self::PREFIX) === false) {
            $tableName = self::PREFIX . $tableName;
        }

        return $this->databaseHelper->getTableNameWithPrefix($tableName);
    }

    /**
     * @param string $oldTable
     * @param string $newTable
     *
     * @return bool
     */
    public function renameTable(string $oldTable, string $newTable): bool
    {
        $oldTable = $this->getFullName($oldTable);
        $newTable = $this->getFullName($newTable);

        if (
            $this->resourceConnection->getConnection()->isTableExists($oldTable) &&
            !$this->resourceConnection->getConnection()->isTableExists($newTable)
        ) {
            $this->resourceConnection->getConnection()->renameTable(
                $oldTable,
                $newTable,
            );

            return true;
        }

        return false;
    }

    /**
     * @param array|string $table
     * @param string $columnName
     *
     * @return string
     */
    public function normalizeEavColumn($table, string $columnName): string
    {
        if (
            $this->stagingHelper->isInstalled() &&
            $this->stagingHelper->isStagedTable($table, ProductAttributeInterface::ENTITY_TYPE_CODE) &&
            strpos($columnName, 'entity_id') !== false
        ) {
            $columnName = str_replace(
                'entity_id',
                $this->stagingHelper->getTableLinkField(ProductAttributeInterface::ENTITY_TYPE_CODE),
                $columnName,
            );
        }

        return $columnName;
    }

    /**
     * @return string[]
     */
    public function getAllTables(): array
    {
        return [
            self::TABLE_NAME_SETUP,
            self::TABLE_NAME_LOCK_ITEM,
            self::TABLE_NAME_LOCK_TRANSACTIONAL,
            self::TABLE_NAME_CONFIG,
            self::TABLE_NAME_ACCOUNT,
            self::TABLE_NAME_LISTING,
            self::TABLE_NAME_LISTING_LOG,
            self::TABLE_NAME_PRODUCT,
            self::TABLE_NAME_PRODUCT_INSTRUCTION,
            self::TABLE_NAME_PRODUCT_SCHEDULED_ACTION,
            self::TABLE_NAME_PROCESSING,
            self::TABLE_NAME_PROCESSING_LOCK,
            self::TABLE_NAME_PROCESSING_PARTIAL_DATA,
            self::TABLE_NAME_STOP_QUEUE,
            self::TABLE_NAME_SYNCHRONIZATION_LOG,
            self::TABLE_NAME_SYSTEM_LOG,
            self::TABLE_NAME_OPERATION_HISTORY,
            self::TABLE_NAME_TEMPLATE_SELLING_FORMAT,
            self::TABLE_NAME_TEMPLATE_SYNCHRONIZATION,
            self::TABLE_NAME_TEMPLATE_DESCRIPTION,
            self::TABLE_NAME_TEMPLATE_SHIPPING,
            self::TABLE_NAME_WIZARD,
            self::TABLE_NAME_REGISTRY,
            self::TABLE_NAME_TAG,
            self::TABLE_NAME_PRODUCT_TAG_RELATION,
            self::TABLE_NAME_ORDER,
            self::TABLE_NAME_ORDER_ITEM,
            self::TABLE_NAME_ORDER_LOG,
            self::TABLE_NAME_ORDER_NOTE,
            self::TABLE_NAME_ORDER_CHANGE,
            self::TABLE_NAME_CATEGORY_GROUP_DICTIONARY,
            self::TABLE_NAME_CATEGORY_DICTIONARY,
            self::TABLE_NAME_CATEGORY_GROUP_ATTRIBUTE_DICTIONARY,
            self::TABLE_NAME_CATEGORY,
            self::TABLE_NAME_CATEGORY_ATTRIBUTES,
            self::TABLE_NAME_BRAND,
            self::TABLE_NAME_LISTING_WIZARD,
            self::TABLE_NAME_LISTING_WIZARD_STEP,
            self::TABLE_NAME_LISTING_WIZARD_PRODUCT,
            self::TABLE_NAME_LISTING_OTHER,
            self::TABLE_NAME_PRODUCT_LOCK
        ];
    }
}