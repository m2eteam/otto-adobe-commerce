<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y24_m06;

use M2E\Otto\Helper\Module\Database\Tables;

class RemoveListingProductConfigurations extends \M2E\Otto\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $config = $this->getConfigModifier();

        $config->delete('/cron/task/listing/product/process_instructions/', 'mode');
    }
}
