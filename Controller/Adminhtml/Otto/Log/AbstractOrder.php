<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Log;

abstract class AbstractOrder extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractMain
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_Otto::sales_logs');
    }
}