<?php

declare(strict_types=1);

namespace M2E\Otto\Model\AdvancedFilter;

class Manager
{
    private \M2E\Otto\Model\Magento\Product\RuleFactory $magentoRuleFactory;
    private \M2E\Otto\Model\Channel\Magento\Product\RuleFactory $channelRuleFactory;
    private Repository $repository;

    public function __construct(
        \M2E\Otto\Model\Magento\Product\RuleFactory $magentoRuleFactory,
        \M2E\Otto\Model\Channel\Magento\Product\RuleFactory $channelRuleFactory,
        Repository $repository
    ) {
        $this->magentoRuleFactory = $magentoRuleFactory;
        $this->channelRuleFactory = $channelRuleFactory;
        $this->repository = $repository;
    }

    public function save(
        \M2E\Otto\Model\Magento\Product\Rule $rule,
        string $title,
        array $conditions
    ): \M2E\Otto\Model\AdvancedFilter {
        $conditions = $this->getSerializedConditions($conditions, $rule);

        return $this->repository->save(
            $rule->getNick(),
            $title,
            $conditions,
            \M2E\Core\Helper\Date::createCurrentGmt()
        );
    }

    public function update(
        \M2E\Otto\Model\AdvancedFilter $advancedFilter,
        string $title,
        array $conditions
    ): void {
        $rule = $this->getRuleModelByNick($advancedFilter->getModelNick());
        $conditions = $this->getSerializedConditions($conditions, $rule);
        $this->repository->update(
            $advancedFilter,
            $title,
            $conditions,
            \M2E\Core\Helper\Date::createCurrentGmt()
        );
    }

    public function isConditionsValid(array $conditions, \M2E\Otto\Model\Magento\Product\Rule $rule): bool
    {
        $conditions = $this->getSerializedConditions($conditions, $rule);
        $rule->loadFromSerialized($conditions);
        if (empty($rule->getConditions()->getConditions())) {
            return false;
        }

        return true;
    }

    private function getSerializedConditions(array $conditions, \M2E\Otto\Model\Magento\Product\Rule $rule): string
    {
        $prefix = $rule->getPrefix();
        $conditionsForSerialize = [
            'rule' => [$prefix => $conditions],
        ];

        return $rule->getSerializedFromPost($conditionsForSerialize);
    }

    public function getRuleWithSavedConditions(
        int $entityId,
        string $modelNick,
        ?int $storeId = null
    ): \M2E\Otto\Model\Magento\Product\Rule {
        $entity = $this->repository->getAdvancedFilter($entityId);
        if ($entity->getModelNick() !== $modelNick) {
            throw new \LogicException('Model nick don`t match');
        }

        $rule = $this->getRuleModelByNick($modelNick, $storeId);
        $rule->loadFromSerialized($entity->getConditionals());

        return $rule;
    }

    public function getRuleModelByNick(string $nick, ?int $storeId = null): \M2E\Otto\Model\Magento\Product\Rule
    {
        if ($nick === \M2E\Otto\Model\Magento\Product\Rule::NICK) {
            return $this->magentoRuleFactory->create(\M2E\Otto\Model\Magento\Product\Rule::NICK, $storeId);
        }

        if ($nick === \M2E\Otto\Model\Channel\Magento\Product\Rule::NICK) {
            return $this->channelRuleFactory
                ->create(\M2E\Otto\Model\Channel\Magento\Product\Rule::NICK, $storeId);
        }

        throw new \LogicException('Unresolved model nick');
    }
}
