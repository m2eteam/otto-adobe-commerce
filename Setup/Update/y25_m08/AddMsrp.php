<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y25_m08;

use M2E\Otto\Model\ResourceModel\Product as ProductResource;
use M2E\Otto\Helper\Module\Database\Tables;
use M2E\Otto\Model\ResourceModel\Template\SellingFormat as SellingResource;

class AddMsrp extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_TEMPLATE_SELLING_FORMAT);

        $modifier->addColumn(
            SellingResource::COLUMN_MSRP_MODE,
            'SMALLINT UNSIGNED NOT NULL',
            0,
            SellingResource::COLUMN_SALE_PRICE_END_DATE_VALUE,
            false,
            false
        );

        $modifier->addColumn(
            SellingResource::COLUMN_MSRP_ATTRIBUTE,
            'VARCHAR(255)',
            null,
            SellingResource::COLUMN_MSRP_MODE,
            false,
            false
        );

        $modifier->commit();

        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);

        $modifier->addColumn(
            ProductResource::COLUMN_ONLINE_MSRP,
            'DECIMAL(12,4) UNSIGNED',
            null,
            null,
            false,
            false
        );

        $modifier->commit();
    }
}
