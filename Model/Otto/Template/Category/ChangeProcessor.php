<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Template\Category;

class ChangeProcessor extends \M2E\Otto\Model\Template\ChangeProcessorAbstract
{
    public const INSTRUCTION_INITIATOR = 'template_category_change_processor';

    protected function getInstructionInitiator(): string
    {
        return self::INSTRUCTION_INITIATOR;
    }

    protected function getInstructionsData(
        \M2E\Otto\Model\ActiveRecord\Diff $diff,
        int $status
    ): array {
        $data = [];
        /** @var \M2E\Otto\Model\Otto\Template\Category\Diff $diff */
        if ($diff->isDifferent()) {
            $data[] = [
                'type' => \M2E\Otto\Model\Template\ChangeProcessorAbstract::INSTRUCTION_TYPE_CATEGORIES_DATA_CHANGED,
                'priority' => $status === \M2E\Otto\Model\Product::STATUS_LISTED ? 30 : 5,
            ];
        }

        return $data;
    }
}
