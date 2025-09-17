<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\InstallHandler;

use M2E\Otto\Helper\Module\Database\Tables as TablesHelper;
use M2E\Otto\Model\ResourceModel\Template\Description as DescriptionResource;
use M2E\Otto\Model\ResourceModel\Template\SellingFormat as SellingFormatResource;
use M2E\Otto\Model\ResourceModel\Template\Shipping as ShippingResource;
use M2E\Otto\Model\ResourceModel\Template\Synchronization as SynchronizationResource;
use Magento\Framework\DB\Ddl\Table;

class PolicyHandler implements \M2E\Core\Model\Setup\InstallHandlerInterface
{
    use \M2E\Otto\Setup\InstallHandlerTrait;

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installTemplateSellingFormatTable($setup);
        $this->installTemplateSynchronizationTable($setup);
        $this->installTemplateDescriptionTable($setup);
        $this->installTemplateShippingTable($setup);
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
    }

    private function installTemplateSellingFormatTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_TEMPLATE_SELLING_FORMAT));

        $table
            ->addColumn(
                SellingFormatResource::COLUMN_ID,
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
                SellingFormatResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_IS_CUSTOM_TEMPLATE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_QTY_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_QTY_CUSTOM_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_QTY_CUSTOM_ATTRIBUTE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_QTY_PERCENTAGE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 100]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_QTY_MODIFICATION_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_QTY_MIN_POSTED_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_QTY_MAX_POSTED_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_FIXED_PRICE_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_FIXED_PRICE_MODIFIER,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_FIXED_PRICE_CUSTOM_ATTRIBUTE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                \M2E\Otto\Model\ResourceModel\Template\SellingFormat::COLUMN_SALE_PRICE_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                \M2E\Otto\Model\ResourceModel\Template\SellingFormat::COLUMN_SALE_PRICE_ATTRIBUTE,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                \M2E\Otto\Model\ResourceModel\Template\SellingFormat::COLUMN_SALE_PRICE_START_DATE_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                \M2E\Otto\Model\ResourceModel\Template\SellingFormat::COLUMN_SALE_PRICE_START_DATE_VALUE,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                \M2E\Otto\Model\ResourceModel\Template\SellingFormat::COLUMN_SALE_PRICE_END_DATE_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                \M2E\Otto\Model\ResourceModel\Template\SellingFormat::COLUMN_SALE_PRICE_END_DATE_VALUE,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                \M2E\Otto\Model\ResourceModel\Template\SellingFormat::COLUMN_MSRP_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                \M2E\Otto\Model\ResourceModel\Template\SellingFormat::COLUMN_MSRP_ATTRIBUTE,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex(
                'is_custom_template',
                SellingFormatResource::COLUMN_IS_CUSTOM_TEMPLATE
            )
            ->addIndex(
                'title',
                SellingFormatResource::COLUMN_TITLE
            )
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installTemplateSynchronizationTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_TEMPLATE_SYNCHRONIZATION));

        $table
            ->addColumn(
                SynchronizationResource::COLUMN_ID,
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
                SynchronizationResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_IS_CUSTOM_TEMPLATE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_STATUS_ENABLED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_IS_IN_STOCK,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_QTY_CALCULATED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_QTY_CALCULATED_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_ADVANCED_RULES_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_ADVANCED_RULES_FILTERS,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_QTY,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_QTY_MAX_APPLIED_VALUE_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_QTY_MAX_APPLIED_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_PRICE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_TITLE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_CATEGORIES,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_IMAGES,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_DESCRIPTION,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_OTHER,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_FILTER_USER_LOCK,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_STATUS_ENABLED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_IS_IN_STOCK,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_QTY_CALCULATED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_QTY_CALCULATED_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_ADVANCED_RULES_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_ADVANCED_RULES_FILTERS,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_STATUS_DISABLED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_OUT_OFF_STOCK,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_QTY_CALCULATED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_QTY_CALCULATED_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_ADVANCED_RULES_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_ADVANCED_RULES_FILTERS,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex(
                'title',
                SynchronizationResource::COLUMN_TITLE
            )
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installTemplateDescriptionTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_TEMPLATE_DESCRIPTION));

        $table
            ->addColumn(
                DescriptionResource::COLUMN_ID,
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
                DescriptionResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                DescriptionResource::COLUMN_IS_CUSTOM_TEMPLATE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                DescriptionResource::COLUMN_TITLE_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                DescriptionResource::COLUMN_TITLE_TEMPLATE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                DescriptionResource::COLUMN_DESCRIPTION_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                DescriptionResource::COLUMN_DESCRIPTION_TEMPLATE,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => false]
            )
            ->addColumn(
                DescriptionResource::COLUMN_IMAGE_MAIN_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                DescriptionResource::COLUMN_IMAGE_MAIN_ATTRIBUTE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                DescriptionResource::COLUMN_GALLERY_TYPE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 4]
            )
            ->addColumn(
                DescriptionResource::COLUMN_GALLERY_IMAGES_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                DescriptionResource::COLUMN_GALLERY_IMAGES_LIMIT,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1]
            )
            ->addColumn(
                DescriptionResource::COLUMN_GALLERY_IMAGES_ATTRIBUTE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                DescriptionResource::COLUMN_BULLET_POINTS,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                DescriptionResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                DescriptionResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex(
                'is_custom_template',
                DescriptionResource::COLUMN_IS_CUSTOM_TEMPLATE
            )
            ->addIndex(
                'title',
                DescriptionResource::COLUMN_TITLE
            )
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installTemplateShippingTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_TEMPLATE_SHIPPING));

        $table->addColumn(
            ShippingResource::COLUMN_ID,
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'primary' => true,
                'nullable' => false,
                'auto_increment' => true,
            ],
        );
        $table->addColumn(
            ShippingResource::COLUMN_ACCOUNT_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => null]
        );
        $table->addColumn(
            ShippingResource::COLUMN_SHIPPING_PROFILE_ID,
            Table::TYPE_TEXT,
            255,
            ['default' => null]
        );
        $table->addColumn(
            ShippingResource::COLUMN_TITLE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false]
        );
        $table->addColumn(
            ShippingResource::COLUMN_IS_CUSTOM_TEMPLATE,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0]
        );
        $table->addColumn(
            ShippingResource::COLUMN_HANDLING_TIME,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
        );
        $table->addColumn(
            ShippingResource::COLUMN_HANDLING_TIME_MODE,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 1]
        );
        $table->addColumn(
            ShippingResource::COLUMN_HANDLING_TIME_ATTRIBUTE,
            Table::TYPE_TEXT,
            255,
            ['default' => null]
        );
        $table->addColumn(
            ShippingResource::COLUMN_TRANSPORT_TIME,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => null],
        );
        $table->addColumn(
            ShippingResource::COLUMN_ORDER_CUTOFF,
            Table::TYPE_TEXT,
            255,
            ['default' => null]
        );
        $table->addColumn(
            ShippingResource::COLUMN_WORKING_DAYS,
            Table::TYPE_TEXT,
            255,
            ['default' => '[]']
        );
        $table->addColumn(
            ShippingResource::COLUMN_TYPE,
            Table::TYPE_TEXT,
            255,
            ['unsigned' => true, 'nullable' => false],
        );
        $table->addColumn(
            ShippingResource::COLUMN_IS_DELETED,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => 0],
        );
        $table->addColumn(
            ShippingResource::COLUMN_UPDATE_DATE,
            Table::TYPE_DATETIME,
            null,
            ['default' => null],
        );
        $table->addColumn(
            ShippingResource::COLUMN_CREATE_DATE,
            Table::TYPE_DATETIME,
            null,
            ['default' => null],
        );
        $table->addIndex('title', ShippingResource::COLUMN_TITLE)
              ->setOption('type', 'INNODB')
              ->setOption('charset', 'utf8')
              ->setOption('collate', 'utf8_general_ci')
              ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }
}
