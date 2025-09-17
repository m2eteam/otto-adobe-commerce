<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template\SellingFormat;

class Diff extends \M2E\Otto\Model\ActiveRecord\Diff
{
    public function isDifferent(): bool
    {
        return $this->isQtyDifferent()
            || $this->isPriceDifferent();
    }

    public function isQtyDifferent(): bool
    {
        $keys = [
            \M2E\Otto\Model\ResourceModel\Template\SellingFormat::COLUMN_QTY_MODE,
            \M2E\Otto\Model\ResourceModel\Template\SellingFormat::COLUMN_QTY_CUSTOM_VALUE,
            \M2E\Otto\Model\ResourceModel\Template\SellingFormat::COLUMN_QTY_CUSTOM_ATTRIBUTE,
            \M2E\Otto\Model\ResourceModel\Template\SellingFormat::COLUMN_QTY_PERCENTAGE,
            \M2E\Otto\Model\ResourceModel\Template\SellingFormat::COLUMN_QTY_MODIFICATION_MODE,
            \M2E\Otto\Model\ResourceModel\Template\SellingFormat::COLUMN_QTY_MIN_POSTED_VALUE,
            \M2E\Otto\Model\ResourceModel\Template\SellingFormat::COLUMN_QTY_MAX_POSTED_VALUE,
        ];

        return $this->isSettingsDifferent($keys);
    }

    public function isPriceDifferent(): bool
    {
        $keys = [
            \M2E\Otto\Model\ResourceModel\Template\SellingFormat::COLUMN_FIXED_PRICE_MODE,
            \M2E\Otto\Model\ResourceModel\Template\SellingFormat::COLUMN_FIXED_PRICE_MODIFIER,
            \M2E\Otto\Model\ResourceModel\Template\SellingFormat::COLUMN_FIXED_PRICE_CUSTOM_ATTRIBUTE,
            \M2E\Otto\Model\ResourceModel\Template\SellingFormat::COLUMN_SALE_PRICE_MODE,
            \M2E\Otto\Model\ResourceModel\Template\SellingFormat::COLUMN_SALE_PRICE_ATTRIBUTE,
            \M2E\Otto\Model\ResourceModel\Template\SellingFormat::COLUMN_SALE_PRICE_START_DATE_MODE,
            \M2E\Otto\Model\ResourceModel\Template\SellingFormat::COLUMN_SALE_PRICE_START_DATE_VALUE,
            \M2E\Otto\Model\ResourceModel\Template\SellingFormat::COLUMN_SALE_PRICE_END_DATE_MODE,
            \M2E\Otto\Model\ResourceModel\Template\SellingFormat::COLUMN_SALE_PRICE_END_DATE_VALUE,
            \M2E\Otto\Model\ResourceModel\Template\SellingFormat::COLUMN_MSRP_MODE,
            \M2E\Otto\Model\ResourceModel\Template\SellingFormat::COLUMN_MSRP_ATTRIBUTE,
        ];

        return $this->isSettingsDifferent($keys);
    }
}
