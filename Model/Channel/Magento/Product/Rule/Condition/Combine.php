<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Channel\Magento\Product\Rule\Condition;

class Combine extends \M2E\Otto\Model\Magento\Product\Rule\Condition\Combine
{
    private const CONDITION_SUFFIX = 'otto';

    protected function getConditionCombine(): string
    {
        return $this->getType() . '|' . self::CONDITION_SUFFIX . '|';
    }

    protected function getCustomLabel(): string
    {
        return (string)__(
            '%channel_title Values',
            ['channel_title' => \M2E\Otto\Helper\Module::getChannelTitle()]
        );
    }

    protected function getCustomOptions(): array
    {
        $attributes = $this->getCustomOptionsAttributes();

        return !empty($attributes)
            ? $this->getOptions(
                \M2E\Otto\Model\Channel\Magento\Product\Rule\Condition\Product::class,
                $attributes,
                [self::CONDITION_SUFFIX]
            )
            : [];
    }

    protected function getCustomOptionsAttributes(): array
    {
        return [
            \M2E\Otto\Model\Magento\Product\Rule\Custom\Otto\OnlineCategory::NICK => __('Category ID'),
            \M2E\Otto\Model\Magento\Product\Rule\Custom\Otto\OnlineQty::NICK => __('Available QTY'),
            \M2E\Otto\Model\Magento\Product\Rule\Custom\Otto\OnlineSku::NICK => __('SKU'),
            \M2E\Otto\Model\Magento\Product\Rule\Custom\Otto\OnlineTitle::NICK => __('Title'),
            \M2E\Otto\Model\Magento\Product\Rule\Custom\Otto\Moin::NICK => __('MOIN'),
            \M2E\Otto\Model\Magento\Product\Rule\Custom\Otto\Status::NICK => __('Status'),
            \M2E\Otto\Model\Magento\Product\Rule\Custom\Otto\OnlinePrice::NICK => __('Price'),
        ];
    }
}
