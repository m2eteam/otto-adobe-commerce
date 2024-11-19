<?php

namespace M2E\Otto\Model\ResourceModel\ActiveRecord;

use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

abstract class CollectionAbstract extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /** @var \M2E\Otto\Helper\Factory */
    protected $helperFactory;

    /** @var \M2E\Otto\Model\ActiveRecord\Factory */
    protected $activeRecordFactory;

    public function __construct(
        \M2E\Otto\Helper\Factory $helperFactory,
        \M2E\Otto\Model\ActiveRecord\Factory $activeRecordFactory,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->helperFactory = $helperFactory;
        $this->activeRecordFactory = $activeRecordFactory;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * @param $helperName
     * @param array $arguments
     *
     * @return \Magento\Framework\App\Helper\AbstractHelper
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    protected function getHelper($helperName, array $arguments = [])
    {
        return $this->helperFactory->getObject($helperName, $arguments);
    }

    public function joinLeft($name, $cond, $cols = '*', $schema = null)
    {
        $cond = $this->replaceJoinCondition($name, $cond);
        $this->getSelect()->joinLeft($name, $cond, $cols, $schema);
    }

    public function joinInner($name, $cond, $cols = '*', $schema = null)
    {
        $cond = $this->replaceJoinCondition($name, $cond);
        $this->getSelect()->joinInner($name, $cond, $cols, $schema);
    }

    /**
     * Compatibility with Magento Enterprise (Staging modules) - entity_id column issue
     */
    private function replaceJoinCondition($table, $cond)
    {
        /** @var \M2E\Otto\Helper\Magento\Staging $helper */
        $helper = $this->getHelper('Magento\Staging');

        if (
            $helper->isInstalled() && $helper->isStagedTable($table) &&
            strpos($cond, 'entity_id') !== false
        ) {
            $linkField = $helper->isStagedTable($table, ProductAttributeInterface::ENTITY_TYPE_CODE)
                ? $helper->getTableLinkField(ProductAttributeInterface::ENTITY_TYPE_CODE)
                : $helper->getTableLinkField(CategoryAttributeInterface::ENTITY_TYPE_CODE);

            $cond = str_replace('entity_id', $linkField, $cond);
        }

        return $cond;
    }

    /**
     * @param null|array $columns
     *
     * @return $this
     */
    public function setColumns($columns = null)
    {
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $columns && $this->getSelect()->columns($columns);

        return $this;
    }

    //########################################
}