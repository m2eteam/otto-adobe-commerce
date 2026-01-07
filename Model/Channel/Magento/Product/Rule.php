<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Channel\Magento\Product;

class Rule extends \M2E\Otto\Model\Magento\Product\Rule
{
    public const NICK = 'otto_product_rule';

    /** @var string */
    protected string $nick = self::NICK;

    private \M2E\Otto\Model\Channel\Magento\Product\Rule\Condition\CombineFactory $ottoRuleCombineFactory;

    public function __construct(
        \M2E\Otto\Model\Channel\Magento\Product\Rule\Condition\CombineFactory $ottoRuleCombineFactory,
        \Magento\Framework\Data\Form $form,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator,
        \M2E\Otto\Model\Magento\Product\Rule\Condition\CombineFactory $ruleConditionCombineFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $form,
            $productFactory,
            $resourceIterator,
            $ruleConditionCombineFactory,
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->ottoRuleCombineFactory = $ottoRuleCombineFactory;
    }

    public function getConditionObj(): \M2E\Otto\Model\Channel\Magento\Product\Rule\Condition\Combine
    {
        return $this->ottoRuleCombineFactory->create();
    }
}
