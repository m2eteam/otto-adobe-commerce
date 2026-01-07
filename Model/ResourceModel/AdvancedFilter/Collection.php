<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\AdvancedFilter;

/**
 * @method \M2E\Otto\Model\AdvancedFilter[] getItems()
 * @method \M2E\Otto\Model\AdvancedFilter getFirstItem()
 */
class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\Otto\Model\AdvancedFilter::class,
            \M2E\Otto\Model\ResourceModel\AdvancedFilter::class
        );
    }

    /**
     * @return \M2E\Otto\Model\AdvancedFilter[]
     */
    public function getAll(): array
    {
        return $this->getItems();
    }
}
