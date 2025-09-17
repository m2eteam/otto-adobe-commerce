<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\InstallHandler;

use M2E\Otto\Helper\Module\Database\Tables as TablesHelper;
use M2E\Otto\Model\ResourceModel\Instruction as ProductInstructionResource;
use M2E\Otto\Model\ResourceModel\Product as ListingProductResource;
use M2E\Otto\Model\ResourceModel\ScheduledAction as ScheduledActionResource;
use M2E\Otto\Model\ResourceModel\Product\Lock as ProductLockResource;
use M2E\Otto\Model\ResourceModel\Listing\Other as ListingOtherResource;
use M2E\Otto\Model\ResourceModel\ExternalChange as ExternalChangeResource;
use Magento\Framework\DB\Ddl\Table;

class ProductHandler implements \M2E\Core\Model\Setup\InstallHandlerInterface
{
    use \M2E\Otto\Setup\InstallHandlerTrait;

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installProductTable($setup);
        $this->installProductInstructionTable($setup);
        $this->installProductScheduledActionTable($setup);
        $this->installProductLockTable($setup);
        $this->installStopQueueTable($setup);
        $this->installUnmanagedProductTable($setup);
        $this->installExternalChangeTable($setup);
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
    }

    private function installProductTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_PRODUCT));

        $table
            ->addColumn(
                ListingProductResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ]
            )
            ->addColumn(
                ListingProductResource::COLUMN_LISTING_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingProductResource::COLUMN_OTTO_PRODUCT_SKU,
                Table::TYPE_TEXT,
                50
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_SKU,
                Table::TYPE_TEXT,
                50
            )
            ->addColumn(
                ListingProductResource::COLUMN_STATUS,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ListingProductResource::COLUMN_STATUS_CHANGE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_IS_INCOMPLETE,
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 0]
            )
            ->addColumn(
                ListingProductResource::COLUMN_STATUS_CHANGER,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_EAN,
                Table::TYPE_TEXT,
                50
            )
            ->addColumn(
                ListingProductResource::COLUMN_PRODUCT_MOIN,
                Table::TYPE_TEXT,
                50,
                ['nullable' => true]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_PRODUCT_REFERENCE,
                Table::TYPE_TEXT,
                100
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_TITLE,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_DESCRIPTION,
                Table::TYPE_TEXT,
                40,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_BRAND_ID,
                Table::TYPE_TEXT,
                30
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_BRAND_NAME,
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_MPN,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_MANUFACTURER,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_PRICE,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_SALE_PRICE,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_SALE_PRICE_START_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_SALE_PRICE_END_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_MSRP,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_QTY,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_VAT,
                Table::TYPE_TEXT,
                50,
                ['nullable' => false]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_DELIVERY_DATA,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_SHIPPING_PROFILE_ID,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_DELIVERY_TYPE,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_OTTO_PRODUCT_URL,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_CATEGORY,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_TEMPLATE_CATEGORY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_CATEGORY_ATTRIBUTES_DATA,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_IMAGES_DATA,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_LAST_BLOCKING_ERROR_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_MARKETPLACE_ERRORS,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ADDITIONAL_DATA,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('listing_id', ListingProductResource::COLUMN_LISTING_ID)
            ->addIndex('magento_product_id', ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID)
            ->addIndex('product_moin', ListingProductResource::COLUMN_PRODUCT_MOIN)
            ->addIndex('status', ListingProductResource::COLUMN_STATUS)
            ->addIndex('status_changer', ListingProductResource::COLUMN_STATUS_CHANGER)
            ->addIndex('online_category', ListingProductResource::COLUMN_ONLINE_CATEGORY)
            ->addIndex('online_title', ListingProductResource::COLUMN_ONLINE_TITLE)
            ->addIndex('template_category_id', ListingProductResource::COLUMN_TEMPLATE_CATEGORY_ID)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installProductInstructionTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_PRODUCT_INSTRUCTION));

        $table
            ->addColumn(
                ProductInstructionResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ]
            )
            ->addColumn(
                ProductInstructionResource::COLUMN_LISTING_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ProductInstructionResource::COLUMN_TYPE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                ProductInstructionResource::COLUMN_INITIATOR,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                ProductInstructionResource::COLUMN_PRIORITY,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ProductInstructionResource::COLUMN_SKIP_UNTIL,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                'create_date',
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('listing_product_id', ProductInstructionResource::COLUMN_LISTING_PRODUCT_ID)
            ->addIndex('type', ProductInstructionResource::COLUMN_TYPE)
            ->addIndex('priority', ProductInstructionResource::COLUMN_PRIORITY)
            ->addIndex('skip_until', ProductInstructionResource::COLUMN_SKIP_UNTIL)
            ->addIndex('create_date', ProductInstructionResource::COLUMN_CREATE_DATE)
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installProductScheduledActionTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_PRODUCT_SCHEDULED_ACTION));

        $table
            ->addColumn(
                ScheduledActionResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_LISTING_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_ACTION_TYPE,
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_STATUS_CHANGER,
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_IS_FORCE,
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_TAG,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_ADDITIONAL_DATA,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex(
                'listing_product_id',
                [ScheduledActionResource::COLUMN_LISTING_PRODUCT_ID],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex('action_type', ScheduledActionResource::COLUMN_ACTION_TYPE)
            ->addIndex('tag', ScheduledActionResource::COLUMN_TAG)
            ->addIndex('create_date', ScheduledActionResource::COLUMN_CREATE_DATE)
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installProductLockTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_PRODUCT_LOCK));

        $table
            ->addColumn(
                ProductLockResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'primary' => true, 'nullable' => false, 'auto_increment' => true]
            )
            ->addColumn(
                ProductLockResource::COLUMN_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                ProductLockResource::COLUMN_INITIATOR,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                ProductLockResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('id', ProductLockResource::COLUMN_ID)
            ->addIndex('product_id', ProductLockResource::COLUMN_PRODUCT_ID)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installStopQueueTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_STOP_QUEUE));

        $table
            ->addColumn(
                \M2E\Otto\Model\ResourceModel\StopQueue::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ]
            )
            ->addColumn(
                \M2E\Otto\Model\ResourceModel\StopQueue::COLUMN_IS_PROCESSED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                \M2E\Otto\Model\ResourceModel\StopQueue::COLUMN_REQUEST_DATA,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                \M2E\Otto\Model\ResourceModel\StopQueue::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                \M2E\Otto\Model\ResourceModel\StopQueue::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex(
                'is_processed',
                \M2E\Otto\Model\ResourceModel\StopQueue::COLUMN_IS_PROCESSED,
            )
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installUnmanagedProductTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_LISTING_OTHER));

        $table
            ->addColumn(
                ListingOtherResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_MOVED_TO_LISTING_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_PRODUCT_REFERENCE,
                Table::TYPE_TEXT,
                100,
                ['nullable' => false]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_SKU,
                Table::TYPE_TEXT,
                50,
                ['nullable' => false]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_EAN,
                Table::TYPE_TEXT,
                100,
                ['nullable' => false]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_MOIN,
                Table::TYPE_TEXT,
                50,
                ['nullable' => true]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_MAGENTO_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_STATUS,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_IS_INCOMPLETE,
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 0]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                70,
                ['nullable' => true]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_CURRENCY,
                Table::TYPE_TEXT,
                10,
                ['default' => null]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_PRICE,
                Table::TYPE_DECIMAL,
                [12, 2],
                ['unsigned' => true, 'nullable' => false, 'default' => '0.00']
            )
            ->addColumn(
                ListingOtherResource::COLUMN_VAT,
                Table::TYPE_TEXT,
                50,
                ['nullable' => false]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_QTY,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_MEDIA,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_CATEGORY,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_BRAND_ID,
                Table::TYPE_TEXT,
                50,
                ['nullable' => false]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_DELIVERY,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => false]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_SHIPPING_PROFILE_ID,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_OTTO_PRODUCT_URL,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_QTY_ACTUALIZE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_PRICE_ACTUALIZE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex(
                'sku',
                ListingOtherResource::COLUMN_SKU,
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex('ean', ListingOtherResource::COLUMN_EAN)
            ->addIndex('moin', ListingOtherResource::COLUMN_MOIN)
            ->addIndex('account_id', ListingOtherResource::COLUMN_ACCOUNT_ID)
            ->addIndex('product_reference', ListingOtherResource::COLUMN_PRODUCT_REFERENCE)
            ->addIndex('magento_product_id', ListingOtherResource::COLUMN_MAGENTO_PRODUCT_ID)
            ->addIndex('status', ListingOtherResource::COLUMN_STATUS)
            ->addIndex('title', ListingOtherResource::COLUMN_TITLE)
            ->addIndex('currency', ListingOtherResource::COLUMN_CURRENCY)
            ->addIndex('price', ListingOtherResource::COLUMN_PRICE)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installExternalChangeTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_EXTERNAL_CHANGE));

        $table
            ->addColumn(
                ExternalChangeResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ]
            )
            ->addColumn(
                ExternalChangeResource::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ExternalChangeResource::COLUMN_SKU,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true]
            )
            ->addColumn(
                ExternalChangeResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('account_id', ExternalChangeResource::COLUMN_ACCOUNT_ID)
            ->addIndex('sku', ExternalChangeResource::COLUMN_SKU)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }
}
