<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Channel\Magento\Product;

class RuleFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(string $prefix, ?int $storeId = null): Rule
    {
        return $this->objectManager->create(Rule::class, [
            'data' => [
                'prefix' => $prefix,
                'store_id' => $storeId,
            ],
        ]);
    }
}
