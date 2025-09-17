<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\DataProvider\Msrp;

use M2E\Otto\Model\Product\DataProvider\SalePrice\Value;

class Result extends \M2E\Otto\Model\Product\DataProvider\AbstractResult
{
    public function getValue(): ?float
    {
        return $this->value;
    }
}
