<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Order\Cancel\Entity;

class Processor
{
    private \M2E\Otto\Model\Connector\Client\Single $singleClient;

    public function __construct(\M2E\Otto\Model\Connector\Client\Single $singleClient)
    {
        $this->singleClient = $singleClient;
    }

    /**
     * @param int[] $ottoOrderItemIds
     *
     * @throws \M2E\Core\Model\Exception\Connection
     * @throws \M2E\Core\Model\Exception\Connection\SystemError
     */
    public function process(
        \M2E\Otto\Model\Account $account,
        string $ottOrderId,
        array $ottoOrderItemIds
    ): \M2E\Core\Model\Connector\Response {
        $command = new Command(
            $account->getServerHash(),
            $ottOrderId,
            $ottoOrderItemIds
        );

        return $this->singleClient->process($command);
    }
}
