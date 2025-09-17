<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y25_m08;

use M2E\Otto\Helper\Module\Database\Tables;

class RemoveOnlineCurrency extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);

        $modifier->dropColumn('online_currency')
                 ->commit();
    }
}
