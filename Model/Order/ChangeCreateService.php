<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order;

class ChangeCreateService
{
    private Change\Repository $repository;
    private ChangeFactory $factory;

    public function __construct(
        \M2E\Otto\Model\Order\Change\Repository $repository,
        ChangeFactory $factory
    ) {
        $this->repository = $repository;
        $this->factory = $factory;
    }

    public function create(
        int $orderId,
        ?int $magentoShipmentId,
        string $action,
        ?int $creatorType,
        array $params
    ): Change {
        $hash = $this->generateHash($orderId, $action, $params);

        $change = $this->repository
            ->findExist($orderId, $magentoShipmentId, $action, $hash);
        if ($change !== null) {
            return $change;
        }

        $change = $this->factory->create();
        $change->init(
            $orderId,
            $magentoShipmentId,
            $action,
            $creatorType,
            $params,
            $hash
        );

        $this->repository->create($change);

        return $change;
    }

    private function generateHash(int $orderId, string $action, array $params): string
    {
        return sha1($orderId . '-' . $action . '-' . json_encode($params, JSON_THROW_ON_ERROR));
    }
}
