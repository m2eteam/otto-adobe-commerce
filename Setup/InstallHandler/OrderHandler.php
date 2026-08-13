<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\InstallHandler;

use M2E\Otto\Helper\Module\Database\Tables as TablesHelper;
use M2E\Otto\Model\ResourceModel\Order as OrderResource;
use M2E\Otto\Model\ResourceModel\Order\Change as OrderChangeResource;
use M2E\Otto\Model\ResourceModel\Order\Item as OrderItemResource;
use M2E\Otto\Model\ResourceModel\Order\Note as OrderNoteResource;
use Magento\Framework\DB\Ddl\Table;

class OrderHandler implements \M2E\Core\Model\Setup\InstallHandlerInterface
{
    use \M2E\Otto\Setup\InstallHandlerTrait;

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installOrderTable($setup);
        $this->installOrderItemTable($setup);
        $this->installOrderNoteTable($setup);
        $this->installOrderChangeTable($setup);
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
    }

    private function installOrderTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_ORDER));

        $table
            ->addColumn(
                'id',
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
                OrderResource::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                'store_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_MAGENTO_ORDER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_MAGENTO_ORDER_CREATION_FAILURE,
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'default' => 0,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_MAGENTO_ORDER_CREATION_FAILS_COUNT,
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'default' => 0,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_MAGENTO_ORDER_CREATION_LATEST_ATTEMPT_DATE,
                Table::TYPE_DATETIME
            )
            ->addColumn(
                'reservation_state',
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'default' => 0,
                ]
            )
            ->addColumn(
                'reservation_start_date',
                Table::TYPE_DATETIME
            )
            ->addColumn(
                'additional_data',
                Table::TYPE_TEXT
            )
            ->addColumn(
                OrderResource::COLUMN_OTTO_ORDER_ID,
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                OrderResource::COLUMN_OTTO_ORDER_NUMBER,
                Table::TYPE_TEXT,
                30
            )
            ->addColumn(
                'order_status',
                Table::TYPE_TEXT,
                30
            )
            ->addColumn(
                'purchase_create_date',
                Table::TYPE_DATETIME
            )
            ->addColumn(
                'purchase_update_date',
                Table::TYPE_DATETIME
            )
            ->addColumn(
                'paid_amount',
                Table::TYPE_DECIMAL,
                [12, 4],
                [
                    'nullable' => false,
                    'unsigned' => true,
                    'default' => 0,
                ]
            )
            ->addColumn(
                'currency',
                Table::TYPE_TEXT,
                10,
                [
                    'nullable' => false,
                ]
            )
            ->addColumn(
                'tax_details',
                Table::TYPE_TEXT
            )
            ->addColumn(
                'buyer_name',
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                'buyer_email',
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                'payment_method_name',
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                'payment_details',
                Table::TYPE_TEXT
            )
            ->addColumn(
                'shipping_details',
                Table::TYPE_TEXT
            )
            ->addColumn(
                'shipping_date_to',
                Table::TYPE_DATETIME
            )
            ->addColumn(
                'create_date',
                Table::TYPE_DATETIME
            )
            ->addColumn(
                'update_date',
                Table::TYPE_DATETIME
            )
            ->addIndex('otto_order_id', 'otto_order_id')
            ->addIndex('buyer_email', 'buyer_email')
            ->addIndex('buyer_name', 'buyer_name')
            ->addIndex('paid_amount', 'paid_amount')
            ->addIndex('purchase_create_date', 'purchase_create_date')
            ->addIndex('shipping_date_to', 'shipping_date_to')
            ->addIndex('account_id', OrderResource::COLUMN_ACCOUNT_ID)
            ->addIndex('magento_order_id', OrderResource::COLUMN_MAGENTO_ORDER_ID)
            ->addIndex('magento_order_creation_failure', OrderResource::COLUMN_MAGENTO_ORDER_CREATION_FAILURE)
            ->addIndex('magento_order_creation_fails_count', OrderResource::COLUMN_MAGENTO_ORDER_CREATION_FAILS_COUNT)
            ->addIndex(
                'magento_order_creation_latest_attempt_date',
                'magento_order_creation_latest_attempt_date'
            )
            ->addIndex('reservation_state', 'reservation_state')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installOrderItemTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_ORDER_ITEM));

        $table
            ->addColumn(
                OrderItemResource::COLUMN_ID,
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
                OrderItemResource::COLUMN_ORDER_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                OrderItemResource::COLUMN_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_PRODUCT_DETAILS,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_QTY_RESERVED,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => 0]
            )
            ->addColumn(
                OrderItemResource::COLUMN_ADDITIONAL_DATA,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_OTTO_ITEM_ID,
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                OrderItemResource::COLUMN_OTTO_PRODUCT_SKU,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                OrderItemResource::COLUMN_ARTICLE_NUMBER,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                OrderItemResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                OrderItemResource::COLUMN_EAN,
                Table::TYPE_TEXT,
                64,
                ['default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_QTY_PURCHASED,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                OrderItemResource::COLUMN_SALE_PRICE,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['nullable' => false, 'default' => '0.0000']
            )
            ->addColumn(
                OrderItemResource::COLUMN_PLATFORM_DISCOUNT,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['nullable' => false, 'default' => '0.0000']
            )
            ->addColumn(
                OrderItemResource::COLUMN_TAX_DETAILS,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                'status',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                OrderItemResource::COLUMN_TRACKING_DETAILS,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_IS_CANCELLATION_IN_PROGRESS,
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => \M2E\Otto\Model\Order\Item::IS_CANCELLATION_IN_PROGRESS_NO,
                ]
            )
            ->addColumn(
                OrderItemResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('otto_item_id', OrderItemResource::COLUMN_OTTO_ITEM_ID)
            ->addIndex('otto_product_sku', OrderItemResource::COLUMN_OTTO_PRODUCT_SKU)
            ->addIndex('article_number', OrderItemResource::COLUMN_ARTICLE_NUMBER)
            ->addIndex('ean', OrderItemResource::COLUMN_EAN)
            ->addIndex('title', OrderItemResource::COLUMN_TITLE)
            ->addIndex('order_id', OrderItemResource::COLUMN_ORDER_ID)
            ->addIndex('product_id', OrderItemResource::COLUMN_PRODUCT_ID)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installOrderNoteTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_ORDER_NOTE));

        $table
            ->addColumn(
                OrderNoteResource::COLUMN_ID,
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
                OrderNoteResource::COLUMN_ORDER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                OrderNoteResource::COLUMN_NOTE,
                Table::TYPE_TEXT,
            )
            ->addColumn(
                OrderNoteResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME
            )
            ->addColumn(
                OrderNoteResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
            )
            ->addIndex('order_id', OrderNoteResource::COLUMN_ORDER_ID)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installOrderChangeTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_ORDER_CHANGE));

        $table
            ->addColumn(
                OrderChangeResource::COLUMN_ID,
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
                OrderChangeResource::COLUMN_ORDER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                OrderChangeResource::COLUMN_MAGENTO_SHIPMENT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                OrderChangeResource::COLUMN_ACTION,
                Table::TYPE_TEXT,
                50,
                ['nullable' => false]
            )
            ->addColumn(
                OrderChangeResource::COLUMN_PARAMS,
                Table::TYPE_TEXT
            )
            ->addColumn(
                OrderChangeResource::COLUMN_CREATOR_TYPE,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false,
                    'default' => 0,
                ]
            )
            ->addColumn(
                OrderChangeResource::COLUMN_PROCESSING_ATTEMPT_COUNT,
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => 0,
                ]
            )
            ->addColumn(
                OrderChangeResource::COLUMN_PROCESSING_ATTEMPT_DATE,
                Table::TYPE_DATETIME,
            )
            ->addColumn(
                OrderChangeResource::COLUMN_HASH,
                Table::TYPE_TEXT,
                50
            )
            ->addColumn(
                OrderChangeResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME
            )
            ->addColumn(
                OrderChangeResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME
            )
            ->addIndex('action', OrderChangeResource::COLUMN_ACTION)
            ->addIndex('creator_type', OrderChangeResource::COLUMN_CREATOR_TYPE)
            ->addIndex('hash', OrderChangeResource::COLUMN_HASH)
            ->addIndex('order_id', OrderChangeResource::COLUMN_ORDER_ID)
            ->addIndex('processing_attempt_count', OrderChangeResource::COLUMN_PROCESSING_ATTEMPT_COUNT)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }
}
