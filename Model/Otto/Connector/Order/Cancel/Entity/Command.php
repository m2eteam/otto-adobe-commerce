<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Order\Cancel\Entity;

class Command implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $accountServerHash;
    private string $ottoOrderId;
    /** @var string[] */
    private array $ottoOrderItemIds;

    /**
     * @param string[] $ottoOrderItemIds
     */
    public function __construct(
        string $accountServerHash,
        string $ottoOrderId,
        array $ottoOrderItemIds
    ) {
        $this->accountServerHash = $accountServerHash;
        $this->ottoOrderId = $ottoOrderId;
        $this->ottoOrderItemIds = $ottoOrderItemIds;
    }

    public function getCommand(): array
    {
        return ['order', 'cancel', 'entity'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountServerHash,
            'order' => [
                'sales_order_id' => $this->ottoOrderId,
                'position_items' => array_map(
                    function (string $ottoOrderItemId) {
                        return [
                            'position_item_id' => $ottoOrderItemId,
                        ];
                    },
                    $this->ottoOrderItemIds
                ),
            ],
        ];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): object
    {
        return $response;
    }
}
