<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Setup\Database\Modifier\Config;

class EntityFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\Otto\Model\Setup\Database\Modifier\Config $config,
        $group,
        $key
    ): Entity {
        return $this->objectManager->create(
            Entity::class,
            [
                'configModifier' => $config,
                'group' => $group,
                'key' => $key,
            ],
        );
    }
}