<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order\Change;

use M2E\Otto\Model\ResourceModel\Order\Change as ChangeResource;

class Repository
{
    private ChangeResource $changeResource;
    private \M2E\Otto\Model\ResourceModel\Order\Change\CollectionFactory $collectionFactory;
    private \M2E\Otto\Model\Order\ChangeFactory $changeFactory;
    private \M2E\Otto\Model\ResourceModel\Order $orderResource;

    public function __construct(
        \M2E\Otto\Model\Order\ChangeFactory $changeFactory,
        ChangeResource $changeResource,
        \M2E\Otto\Model\ResourceModel\Order\Change\CollectionFactory $collectionFactory,
        \M2E\Otto\Model\ResourceModel\Order $orderResource
    ) {
        $this->changeFactory = $changeFactory;
        $this->orderResource = $orderResource;
        $this->changeResource = $changeResource;
        $this->collectionFactory = $collectionFactory;
    }

    public function create(\M2E\Otto\Model\Order\Change $change): void
    {
        $this->changeResource->save($change);
    }

    public function save(\M2E\Otto\Model\Order\Change $change): void
    {
        $this->changeResource->save($change);
    }

    /**
     * @param int $orderId
     * @param int $magentoShipmentId
     *
     * @return \M2E\Otto\Model\Order\Change[]
     */
    public function findWithActionShippingByOrder(int $orderId, int $magentoShipmentId): array
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter(ChangeResource::COLUMN_ORDER_ID, $orderId)
            ->addFieldToFilter(ChangeResource::COLUMN_MAGENTO_SHIPMENT_ID, $magentoShipmentId)
            ->addFieldToFilter(
                ChangeResource::COLUMN_ACTION,
                \M2E\Otto\Model\Order\Change::ACTION_UPDATE_SHIPPING,
            );

        return array_values($collection->getItems());
    }

    /**
     * @deprecated Use \M2E\Otto\Model\Order\ChangeCreateService::create for create order_change
     */
    public function createShippingOrUpdateNotProcessed(
        \M2E\Otto\Model\Order $order,
        array $params,
        ?int $initiator,
        int $magentoShipmentId
    ): void {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(ChangeResource::COLUMN_ORDER_ID, $order->getId())
                   ->addFieldToFilter(ChangeResource::COLUMN_MAGENTO_SHIPMENT_ID, $magentoShipmentId)
                   ->addFieldToFilter(
                       ChangeResource::COLUMN_ACTION,
                       \M2E\Otto\Model\Order\Change::ACTION_UPDATE_SHIPPING,
                   )
                   ->addFieldToFilter(ChangeResource::COLUMN_PROCESSING_ATTEMPT_COUNT, 0);

        $change = $collection->getFirstItem();
        if ($change->isObjectNew()) {
            $change = $this->changeFactory->create();
            $change->init(
                $order->getId(),
                $magentoShipmentId,
                \M2E\Otto\Model\Order\Change::ACTION_UPDATE_SHIPPING,
                $initiator,
                $params,
                $this->generateHash($order->getId(), \M2E\Otto\Model\Order\Change::ACTION_UPDATE_SHIPPING, $params),
            );

            $this->changeResource->save($change);

            return;
        }

        $change->setParams($params);

        $this->changeResource->save($change);
    }

    /**
     * @param \M2E\Otto\Model\Account $account
     * @param int $limit
     *
     * @return \M2E\Otto\Model\Order\Change[]
     */
    public function findShippingForProcess(\M2E\Otto\Model\Account $account, int $limit): array
    {
        return $this->findReadyForProcessByAction(\M2E\Otto\Model\Order\Change::ACTION_UPDATE_SHIPPING, $account, $limit);
    }

    public function delete(\M2E\Otto\Model\Order\Change $change): void
    {
        $this->changeResource->delete($change);
    }

    public function findExist(int $orderId, ?int $magentoShipmentId, string $action, string $hash): ?\M2E\Otto\Model\Order\Change
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter(
                ChangeResource::COLUMN_ORDER_ID,
                ['eq' => $orderId]
            )
            ->addFieldToFilter(
                ChangeResource::COLUMN_MAGENTO_SHIPMENT_ID,
                ['eq' => $magentoShipmentId]
            )
            ->addFieldToFilter(
                ChangeResource::COLUMN_ACTION,
                ['eq' => $action]
            )
            ->addFieldToFilter(
                ChangeResource::COLUMN_HASH,
                ['eq' => $hash]
            )
            ->setPageSize(1);

        $change = $collection->getFirstItem();
        if ($change->isObjectNew()) {
            return null;
        }

        return $change;
    }

    /**
     * @return \M2E\Otto\Model\Order\Change[]
     */
    public function findCanceledReadyForProcess(\M2E\Otto\Model\Account $account, int $limit): array
    {
        return $this->findReadyForProcessByAction(\M2E\Otto\Model\Order\Change::ACTION_CANCEL, $account, $limit);
    }

    private function findReadyForProcessByAction(
        string $action,
        \M2E\Otto\Model\Account $account,
        int $limit
    ): array {
        $collection = $this->collectionFactory->create();
        $collection->joinInner(
            ['orders' => $this->orderResource->getMainTable()],
            sprintf(
                '`orders`.`%s` = `main_table`.`%s`',
                \M2E\Otto\Model\ResourceModel\Order::COLUMN_ID,
                ChangeResource::COLUMN_ORDER_ID,
            ),
            [
                'account_id' => \M2E\Otto\Model\ResourceModel\Order::COLUMN_ACCOUNT_ID,
            ],
        );
        $collection->addFieldToFilter(
            sprintf('orders.%s', \M2E\Otto\Model\ResourceModel\Order::COLUMN_ACCOUNT_ID),
            ['eq' => $account->getId()],
        );

        $hourAgo = \M2E\Core\Helper\Date::createCurrentGmt();
        $hourAgo->modify('-1 hour');

        $collection->addFieldToFilter(
            [
                ChangeResource::COLUMN_PROCESSING_ATTEMPT_DATE,
                ChangeResource::COLUMN_PROCESSING_ATTEMPT_DATE,
            ],
            [
                ['null' => true],
                ['lteq' => $hourAgo->format('Y-m-d H:i:s')],
            ],
        );

        $collection->addFieldToFilter(
            ChangeResource::COLUMN_ACTION,
            ['eq' => $action],
        );

        $collection->setPageSize($limit);
        $collection->getSelect()->group(['main_table.id']);

        return array_values($collection->getItems());
    }

    private function generateHash($orderId, $action, array $params): string
    {
        return sha1($orderId . '-' . $action . '-' . json_encode($params, JSON_THROW_ON_ERROR));
    }

    /**
     * @return \M2E\Otto\Model\Order\Change[]
     */
    public function getItemsExceededProcessingAttemptCount(int $count, string $action): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            ChangeResource::COLUMN_PROCESSING_ATTEMPT_COUNT,
            ['gteq' => $count]
        );
        $collection->addFieldToFilter(
            ChangeResource::COLUMN_ACTION,
            ['eq' => $action],
        );

        return array_values($collection->getItems());
    }
}
