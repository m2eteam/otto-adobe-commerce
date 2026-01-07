<?php

declare(strict_types=1);

namespace M2E\Otto\Helper\Module\Database;

use Magento\Catalog\Api\Data\ProductAttributeInterface;

class Tables
{
    public const PREFIX = 'm2e_otto_';

    public const TABLE_NAME_WIZARD = self::PREFIX . 'wizard';
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
    public const TABLE_NAME_EXTERNAL_CHANGE = self::PREFIX . 'external_change';
    public const TABLE_NAME_ADVANCED_FILTER = self::PREFIX . 'advanced_filter';

    private \Magento\Framework\App\ResourceConnection $resourceConnection;
    private Structure $databaseHelper;
    private \M2E\Core\Helper\Magento\Staging $stagingHelper;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \M2E\Otto\Helper\Module\Database\Structure $databaseHelper,
        \M2E\Core\Helper\Magento\Staging $stagingHelper
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

    // ----------------------------------------

    /**
     * @return string[]
     */
    public static function getAllTables(): array
    {
        return array_keys(self::getTablesResourcesModels());
    }

    public static function getTableResourceModel(string $tableName): string
    {
        $tablesModels = self::getTablesResourcesModels();

        return $tablesModels[$tableName];
    }

    private static function getTablesResourcesModels(): array
    {
        return [
            self::TABLE_NAME_WIZARD => \M2E\Otto\Model\ResourceModel\Wizard::class,
            self::TABLE_NAME_ACCOUNT => \M2E\Otto\Model\ResourceModel\Account::class,
            self::TABLE_NAME_LISTING => \M2E\Otto\Model\ResourceModel\Listing::class,
            self::TABLE_NAME_LISTING_LOG => \M2E\Otto\Model\ResourceModel\Listing\Log::class,
            self::TABLE_NAME_PRODUCT => \M2E\Otto\Model\ResourceModel\Product::class,
            self::TABLE_NAME_PRODUCT_INSTRUCTION => \M2E\Otto\Model\ResourceModel\Instruction::class,
            self::TABLE_NAME_PRODUCT_SCHEDULED_ACTION => \M2E\Otto\Model\ResourceModel\ScheduledAction::class,
            self::TABLE_NAME_LISTING_WIZARD => \M2E\Otto\Model\ResourceModel\Listing\Wizard::class,
            self::TABLE_NAME_LISTING_WIZARD_STEP => \M2E\Otto\Model\ResourceModel\Listing\Wizard\Step::class,
            self::TABLE_NAME_LISTING_WIZARD_PRODUCT => \M2E\Otto\Model\ResourceModel\Listing\Wizard\Product::class,
            self::TABLE_NAME_PRODUCT_LOCK => \M2E\Otto\Model\ResourceModel\Product\Lock::class,
            self::TABLE_NAME_LOCK_ITEM => \M2E\Otto\Model\ResourceModel\Lock\Item::class,
            self::TABLE_NAME_LOCK_TRANSACTIONAL => \M2E\Otto\Model\ResourceModel\Lock\Transactional::class,
            self::TABLE_NAME_PROCESSING => \M2E\Otto\Model\ResourceModel\Processing::class,
            self::TABLE_NAME_PROCESSING_PARTIAL_DATA => \M2E\Otto\Model\ResourceModel\Processing\PartialData::class,
            self::TABLE_NAME_PROCESSING_LOCK => \M2E\Otto\Model\ResourceModel\Processing\Lock::class,
            self::TABLE_NAME_STOP_QUEUE => \M2E\Otto\Model\ResourceModel\StopQueue::class,
            self::TABLE_NAME_SYNCHRONIZATION_LOG => \M2E\Otto\Model\ResourceModel\Synchronization\Log::class,
            self::TABLE_NAME_SYSTEM_LOG => \M2E\Otto\Model\ResourceModel\Log\System::class,
            self::TABLE_NAME_OPERATION_HISTORY => \M2E\Otto\Model\ResourceModel\OperationHistory::class,
            self::TABLE_NAME_TEMPLATE_SELLING_FORMAT => \M2E\Otto\Model\ResourceModel\Template\SellingFormat::class,
            self::TABLE_NAME_TEMPLATE_SYNCHRONIZATION => \M2E\Otto\Model\ResourceModel\Template\Synchronization::class,
            self::TABLE_NAME_TEMPLATE_DESCRIPTION => \M2E\Otto\Model\ResourceModel\Template\Description::class,
            self::TABLE_NAME_TEMPLATE_SHIPPING => \M2E\Otto\Model\ResourceModel\Template\Shipping::class,
            self::TABLE_NAME_TAG => \M2E\Otto\Model\ResourceModel\Tag::class,
            self::TABLE_NAME_PRODUCT_TAG_RELATION => \M2E\Otto\Model\ResourceModel\Tag\ListingProduct\Relation::class,
            self::TABLE_NAME_ORDER => \M2E\Otto\Model\ResourceModel\Order::class,
            self::TABLE_NAME_ORDER_ITEM => \M2E\Otto\Model\ResourceModel\Order\Item::class,
            self::TABLE_NAME_ORDER_LOG => \M2E\Otto\Model\ResourceModel\Order\Log::class,
            self::TABLE_NAME_ORDER_NOTE => \M2E\Otto\Model\ResourceModel\Order\Note::class,
            self::TABLE_NAME_ORDER_CHANGE => \M2E\Otto\Model\ResourceModel\Order\Change::class,
            self::TABLE_NAME_LISTING_OTHER => \M2E\Otto\Model\ResourceModel\Listing\Other::class,
            self::TABLE_NAME_CATEGORY_GROUP_DICTIONARY => \M2E\Otto\Model\ResourceModel\Dictionary\CategoryGroup::class,
            self::TABLE_NAME_CATEGORY_DICTIONARY => \M2E\Otto\Model\ResourceModel\Dictionary\Category::class,
            self::TABLE_NAME_CATEGORY_GROUP_ATTRIBUTE_DICTIONARY => \M2E\Otto\Model\ResourceModel\Dictionary\Attribute::class,
            self::TABLE_NAME_CATEGORY => \M2E\Otto\Model\ResourceModel\Category::class,
            self::TABLE_NAME_CATEGORY_ATTRIBUTES => \M2E\Otto\Model\ResourceModel\Category\Attribute::class,
            self::TABLE_NAME_BRAND => \M2E\Otto\Model\ResourceModel\Brand::class,
            self::TABLE_NAME_EXTERNAL_CHANGE => \M2E\Otto\Model\ResourceModel\ExternalChange::class,
            self::TABLE_NAME_ADVANCED_FILTER => \M2E\Otto\Model\ResourceModel\AdvancedFilter::class,
        ];
    }

    public static function getTableModel(string $tableName): string
    {
        $tablesModels = self::getTablesModels();

        return $tablesModels[$tableName];
    }

    private static function getTablesModels(): array
    {
        return [
            self::TABLE_NAME_WIZARD => \M2E\Otto\Model\Wizard::class,
            self::TABLE_NAME_ACCOUNT => \M2E\Otto\Model\Account::class,
            self::TABLE_NAME_LISTING => \M2E\Otto\Model\Listing::class,
            self::TABLE_NAME_LISTING_LOG => \M2E\Otto\Model\Listing\Log::class,
            self::TABLE_NAME_PRODUCT => \M2E\Otto\Model\Product::class,
            self::TABLE_NAME_PRODUCT_INSTRUCTION => \M2E\Otto\Model\Instruction::class,
            self::TABLE_NAME_PRODUCT_SCHEDULED_ACTION => \M2E\Otto\Model\ScheduledAction::class,
            self::TABLE_NAME_LISTING_WIZARD => \M2E\Otto\Model\Listing\Wizard::class,
            self::TABLE_NAME_LISTING_WIZARD_STEP => \M2E\Otto\Model\Listing\Wizard\Step::class,
            self::TABLE_NAME_LISTING_WIZARD_PRODUCT => \M2E\Otto\Model\Listing\Wizard\Product::class,
            self::TABLE_NAME_PRODUCT_LOCK => \M2E\Otto\Model\Product\Lock::class,
            self::TABLE_NAME_LOCK_ITEM => \M2E\Otto\Model\Lock\Item::class,
            self::TABLE_NAME_LOCK_TRANSACTIONAL => \M2E\Otto\Model\Lock\Transactional::class,
            self::TABLE_NAME_PROCESSING => \M2E\Otto\Model\Processing::class,
            self::TABLE_NAME_PROCESSING_PARTIAL_DATA => \M2E\Otto\Model\Processing\PartialData::class,
            self::TABLE_NAME_PROCESSING_LOCK => \M2E\Otto\Model\Processing\Lock::class,
            self::TABLE_NAME_STOP_QUEUE => \M2E\Otto\Model\StopQueue::class,
            self::TABLE_NAME_SYNCHRONIZATION_LOG => \M2E\Otto\Model\Synchronization\Log::class,
            self::TABLE_NAME_SYSTEM_LOG => \M2E\Otto\Model\Log\System::class,
            self::TABLE_NAME_OPERATION_HISTORY => \M2E\Otto\Model\OperationHistory::class,
            self::TABLE_NAME_TEMPLATE_SELLING_FORMAT => \M2E\Otto\Model\Template\SellingFormat::class,
            self::TABLE_NAME_TEMPLATE_SYNCHRONIZATION => \M2E\Otto\Model\Template\Synchronization::class,
            self::TABLE_NAME_TEMPLATE_DESCRIPTION => \M2E\Otto\Model\Template\Description::class,
            self::TABLE_NAME_TEMPLATE_SHIPPING => \M2E\Otto\Model\Template\Shipping::class,
            self::TABLE_NAME_TAG => \M2E\Otto\Model\Tag\Entity::class,
            self::TABLE_NAME_PRODUCT_TAG_RELATION => \M2E\Otto\Model\Tag\ListingProduct\Relation::class,
            self::TABLE_NAME_ORDER => \M2E\Otto\Model\Order::class,
            self::TABLE_NAME_ORDER_ITEM => \M2E\Otto\Model\Order\Item::class,
            self::TABLE_NAME_ORDER_LOG => \M2E\Otto\Model\Order\Log::class,
            self::TABLE_NAME_ORDER_NOTE => \M2E\Otto\Model\Order\Note::class,
            self::TABLE_NAME_ORDER_CHANGE => \M2E\Otto\Model\Order\Change::class,
            self::TABLE_NAME_LISTING_OTHER => \M2E\Otto\Model\Listing\Other::class,
            self::TABLE_NAME_CATEGORY_GROUP_DICTIONARY => \M2E\Otto\Model\Dictionary\CategoryGroup::class,
            self::TABLE_NAME_CATEGORY_DICTIONARY => \M2E\Otto\Model\Dictionary\Category::class,
            self::TABLE_NAME_CATEGORY_GROUP_ATTRIBUTE_DICTIONARY => \M2E\Otto\Model\Dictionary\Attribute::class,
            self::TABLE_NAME_CATEGORY => \M2E\Otto\Model\Category::class,
            self::TABLE_NAME_CATEGORY_ATTRIBUTES => \M2E\Otto\Model\Category\Attribute::class,
            self::TABLE_NAME_BRAND => \M2E\Otto\Model\Brand::class,
            self::TABLE_NAME_EXTERNAL_CHANGE => \M2E\Otto\Model\ExternalChange::class,
            self::TABLE_NAME_ADVANCED_FILTER => \M2E\Otto\Model\AdvancedFilter::class,
        ];
    }

    public static function isModuleTable(string $tableName): bool
    {
        return strpos($tableName, self::PREFIX) !== false;
    }
}
