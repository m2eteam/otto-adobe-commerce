<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template\SellingFormat;

class Repository
{
    private \M2E\Otto\Model\ResourceModel\Template\SellingFormat $resource;
    private \M2E\Otto\Model\Template\SellingFormatFactory $sellingFormatFactory;
    private \M2E\Otto\Model\ResourceModel\Template\SellingFormat\CollectionFactory $collectionFactory;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Template\SellingFormat $resource,
        \M2E\Otto\Model\Template\SellingFormatFactory $sellingFormatFactory,
        \M2E\Otto\Model\ResourceModel\Template\SellingFormat\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->sellingFormatFactory = $sellingFormatFactory;
    }

    public function find(int $id): ?\M2E\Otto\Model\Template\SellingFormat
    {
        $model = $this->sellingFormatFactory->create();
        $this->resource->load($model, $id);

        if ($model->isObjectNew()) {
            return null;
        }

        return $model;
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function get(int $id): \M2E\Otto\Model\Template\SellingFormat
    {
        $template = $this->find($id);
        if ($template === null) {
            throw new \M2E\Otto\Model\Exception\Logic(
                sprintf('Selling Policy with ID %d not found.', $id)
            );
        }

        return $template;
    }

    public function delete(\M2E\Otto\Model\Template\SellingFormat $template)
    {
        $this->resource->delete($template);
    }

    public function create(\M2E\Otto\Model\Template\SellingFormat $template)
    {
        $this->resource->save($template);
    }

    public function save(\M2E\Otto\Model\Template\SellingFormat $template)
    {
        $this->resource->save($template);
    }

    /**
     * @return \M2E\Otto\Model\Template\SellingFormat[]
     */
    public function getAll(): array
    {
        $collection = $this->collectionFactory->create();

        return array_values($collection->getItems());
    }
}
