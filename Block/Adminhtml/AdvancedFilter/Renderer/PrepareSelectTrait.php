<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\AdvancedFilter\Renderer;

trait PrepareSelectTrait
{
    /**
     * @param \M2E\Otto\Model\AdvancedFilter[] $entities
     *
     * @return array
     */
    private function createSelect(array $entities): array
    {
        $result = [''];

        $byId = [];
        foreach ($entities as $entity) {
            $byId[$entity->getId()] = $entity->getTitle();
        }

        asort($byId);

        return $result + $byId;
    }
}
