<?php

declare(strict_types=1);

namespace M2E\Otto\Helper\Magento;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Framework\Config\ConfigOptionsListConstants;

class Staging
{
    private \Magento\Framework\Module\FullModuleList $fullModuleList;
    private \Magento\Framework\App\DeploymentConfig $deploymentConfig;
    private \Magento\Framework\App\ResourceConnection $resourceConnection;
    private \M2E\Otto\Helper\Module\Database\Structure $dbStructure;

    public function __construct(
        \M2E\Otto\Helper\Module\Database\Structure $dbStructure,
        \Magento\Framework\Module\FullModuleList $fullModuleList,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->fullModuleList = $fullModuleList;
        $this->deploymentConfig = $deploymentConfig;
        $this->resourceConnection = $resourceConnection;
        $this->dbStructure = $dbStructure;
    }

    // ----------------------------------------

    public function isInstalled()
    {
        return $this->fullModuleList->getOne('Magento_CatalogStaging');
    }

    //----------------------------------------

    public function getStagedTables($entityType)
    {
        $tables = [
            $entityType . '_entity_varchar',
            $entityType . '_entity_int',
            $entityType . '_entity_text',
            $entityType . '_entity_datetime',
            $entityType . '_entity_decimal',
        ];

        if ($entityType === ProductAttributeInterface::ENTITY_TYPE_CODE) {
            $tables[] = $entityType . '_entity_gallery';
        }

        return $tables;
    }

    /**
     * @param $tableName
     * @param $entityType
     *
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function isStagedTable($tableName, $entityType = null): bool
    {
        $tableName = is_array($tableName) ? $tableName[key($tableName)] : $tableName;
        $tableName = str_replace(
            (string)$this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_DB_PREFIX),
            '',
            $tableName
        );

        if (!$entityType) {
            return in_array($tableName, $this->getStagedTables(ProductAttributeInterface::ENTITY_TYPE_CODE)) ||
                in_array($tableName, $this->getStagedTables(CategoryAttributeInterface::ENTITY_TYPE_CODE));
        }

        return in_array($tableName, $this->getStagedTables($entityType));
    }

    /**
     * @param $entityType
     *
     * @return mixed
     */
    public function getTableLinkField($entityType)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->dbStructure->getTableNameWithPrefix($entityType . '_entity');

        $indexList = $connection->getIndexList($tableName);

        return $indexList[$connection->getPrimaryKeyName($tableName)]['COLUMNS_LIST'][0];
    }
}
