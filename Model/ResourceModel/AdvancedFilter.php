<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel;

class AdvancedFilter extends \M2E\Otto\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_MODEL_NICK = 'model_nick';
    public const COLUMN_TITLE = 'title';
    public const COLUMN_CONDITIONALS = 'conditionals';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    protected function _construct()
    {
        $this->_init(
            \M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_ADVANCED_FILTER,
            self::COLUMN_ID
        );
    }
}
