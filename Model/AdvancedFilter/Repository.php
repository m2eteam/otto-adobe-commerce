<?php

declare(strict_types=1);

namespace M2E\Otto\Model\AdvancedFilter;

use M2E\Otto\Model\ResourceModel\AdvancedFilter as AdvancedFilterResource;

class Repository
{
    private \M2E\Otto\Model\ResourceModel\AdvancedFilter\CollectionFactory $collectionFactory;
    private \M2E\Otto\Model\AdvancedFilter $advancedFilter;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\AdvancedFilter\CollectionFactory $collectionFactory,
        \M2E\Otto\Model\AdvancedFilter $advancedFilter
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->advancedFilter = $advancedFilter;
    }

    public function getAdvancedFilter(int $id): \M2E\Otto\Model\AdvancedFilter
    {
        $collection = $this->collectionFactory->create();
        $collection->addFilter(AdvancedFilterResource::COLUMN_ID, $id);
        $collection->getSelect()->limit(1);

        $advancedFilter = $collection->getFirstItem();
        if ($advancedFilter->isEmpty()) {
            throw new \M2E\Otto\Model\Exception\Logic(sprintf('Not found entity by id - [%d]', $id));
        }

        return $advancedFilter;
    }

    /**
     * @return \M2E\Otto\Model\AdvancedFilter[]
     */
    public function findItemsByModelNick(string $modelNick): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFilter(AdvancedFilterResource::COLUMN_MODEL_NICK, $modelNick);

        return array_values($collection->getItems());
    }

    public function isExistItemsWithModelNick(string $modelNick): bool
    {
        $collection = $this->collectionFactory->create();
        $collection->addFilter(AdvancedFilterResource::COLUMN_MODEL_NICK, $modelNick);

        return (bool)$collection->getSize();
    }

    public function update(
        \M2E\Otto\Model\AdvancedFilter $advancedFilter,
        string $title,
        string $conditions,
        \DateTime $updateDate
    ): void {
        $advancedFilter->setTitle($title);
        $advancedFilter->setConditionals($conditions);
        $advancedFilter->setUpdateDate($updateDate);

        $advancedFilter->save();
    }

    public function save(
        string $modelNick,
        string $title,
        string $conditionals,
        \DateTime $createDate
    ): \M2E\Otto\Model\AdvancedFilter {
        $this->advancedFilter->setModelNick($modelNick);
        $this->advancedFilter->setTitle($title);
        $this->advancedFilter->setConditionals($conditionals);
        $this->advancedFilter->setCreateDate($createDate);
        $this->advancedFilter->setUpdateDate($createDate);

        $this->advancedFilter->save();

        return $this->advancedFilter;
    }

    public function remove(\M2E\Otto\Model\AdvancedFilter $advancedFilter): void
    {
        $advancedFilter->delete();
    }
}
