<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Lock;

class Transactional extends \M2E\Otto\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_NICK = 'nick';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct(): void
    {
        $this->_init(\M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_LOCK_TRANSACTIONAL, self::COLUMN_ID);
    }
}
