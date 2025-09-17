<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Template\Category;

class Grid extends \M2E\Otto\Controller\Adminhtml\Otto\Template\AbstractCategory
{
    public function execute()
    {
        /** @var \M2E\Otto\Block\Adminhtml\Otto\Template\Category\Grid $grid */
        $grid = $this->getLayout()->createBlock(
            \M2E\Otto\Block\Adminhtml\Otto\Template\Category\Grid::class
        );

        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
