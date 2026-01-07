<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\InstallHandler;

use M2E\Otto\Helper\Module\Database\Tables as TablesHelper;
use M2E\Otto\Model\ResourceModel\AdvancedFilter as AdvancedFilterResource;
use Magento\Framework\DB\Ddl\Table;

class AdvancedFilterHandler implements \M2E\Core\Model\Setup\InstallHandlerInterface
{
    use \M2E\Otto\Setup\InstallHandlerTrait;

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installAdvancedFilterTable($setup);
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
    }

    private function installAdvancedFilterTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_ADVANCED_FILTER);

        $tagTable = $setup->getConnection()->newTable($tableName);
        $tagTable
            ->addColumn(
                AdvancedFilterResource::COLUMN_ID,
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
                AdvancedFilterResource::COLUMN_MODEL_NICK,
                Table::TYPE_TEXT,
                100,
                ['nullable' => false]
            )
            ->addColumn(
                AdvancedFilterResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                AdvancedFilterResource::COLUMN_CONDITIONALS,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => false]
            )
            ->addColumn(
                AdvancedFilterResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                AdvancedFilterResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false]
            );

        $tagTable->setOption('type', 'INNODB');
        $tagTable->setOption('charset', 'utf8');
        $tagTable->setOption('collate', 'utf8_general_ci');
        $tagTable->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($tagTable);
    }
}
