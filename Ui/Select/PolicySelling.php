<?php

declare(strict_types=1);

namespace M2E\Otto\Ui\Select;

class PolicySelling implements \Magento\Framework\Data\OptionSourceInterface
{
    private \M2E\Otto\Model\Template\SellingFormat\Repository $repository;

    public function __construct(\M2E\Otto\Model\Template\SellingFormat\Repository $repository)
    {
        $this->repository = $repository;
    }

    public function toOptionArray(): array
    {
        $options = [];
        foreach ($this->repository->getAll() as $policy) {
            $options[] = [
                'label' => $policy->getTitle(),
                'value' => $policy->getId(),
            ];
        }

        return $options;
    }
}
