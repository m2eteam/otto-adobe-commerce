<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y26_m08;

use M2E\Otto\Helper\Module\Database\Tables;

class CancelOrderItemsIfCreditMemoCreated extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->changeOrderChangeMagentoShipmentIdDefinition();
        $this->addColumnIsCancellationInProgress();
    }

    private function changeOrderChangeMagentoShipmentIdDefinition(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_ORDER_CHANGE);
        $modifier->changeColumn(
            \M2E\Otto\Model\ResourceModel\Order\Change::COLUMN_MAGENTO_SHIPMENT_ID,
            'INT UNSIGNED DEFAULT NULL',
            'NULL',
            \M2E\Otto\Model\ResourceModel\Order\Change::COLUMN_ORDER_ID,
            false
        );
        $modifier->commit();
    }

    private function addColumnIsCancellationInProgress(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_ORDER_ITEM);
        $modifier->addColumn(
            \M2E\Otto\Model\ResourceModel\Order\Item::COLUMN_IS_CANCELLATION_IN_PROGRESS,
            'SMALLINT UNSIGNED NOT NULL',
            \M2E\Otto\Model\Order\Item::IS_CANCELLATION_IN_PROGRESS_NO,
            null,
            false,
            false
        );
        $modifier->commit();
    }
}
