<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template\Description;

use M2E\Otto\Model\Otto\Template\AffectedListingsProducts\AffectedListingsProductsAbstract;

class AffectedListingsProducts extends AffectedListingsProductsAbstract
{
    public function getTemplateNick(): string
    {
        return \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_DESCRIPTION;
    }
}