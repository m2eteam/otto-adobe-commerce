<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product;

use M2E\Otto\Model\Product;

class UpdateFromChannel
{
    private \M2E\Otto\Model\Product\Repository $repository;
    private \M2E\Otto\Model\InstructionService $instructionService;
    private \M2E\Otto\Model\Listing\LogService $logService;
    private \M2E\Otto\Model\Product\UpdateFromChannel\ProcessorFactory $changesProcessorFactory;
    private int $logActionId;

    public function __construct(
        Repository $repository,
        \M2E\Otto\Model\InstructionService $instructionService,
        \M2E\Otto\Model\Listing\LogService $logService,
        \M2E\Otto\Model\Product\UpdateFromChannel\ProcessorFactory $changesProcessorFactory
    ) {
        $this->repository = $repository;
        $this->instructionService = $instructionService;
        $this->logService = $logService;
        $this->changesProcessorFactory = $changesProcessorFactory;
    }

    public function process(
        \M2E\Otto\Model\Listing\Other\OttoProductCollection $channelProductCollection,
        \M2E\Otto\Model\Account $account
    ): void {
        if ($channelProductCollection->empty()) {
            return;
        }

        $existed = $this->repository->findByOttoProductSKUs(
            $channelProductCollection->getProductsSKUs(),
            $account->getId(),
        );

        foreach ($existed as $product) {
            if (!$channelProductCollection->has($product->getOttoProductSku())) {
                continue;
            }

            $channelProduct = $channelProductCollection->get($product->getOttoProductSku());

            $changesProcessor = $this->changesProcessorFactory->create($product, $channelProduct);

            $changeResult = $changesProcessor->processChanges();

            if ($changeResult->isChangedProduct()) {
                $this->repository->save($product);
            }

            $this->writeInstructions($changeResult->getInstructionsData());
            $this->writeLogs($product, $changeResult->getLogs());
        }
    }

    private function writeInstructions(array $instructionsData): void
    {
        if (empty($instructionsData)) {
            return;
        }

        $this->instructionService->createBatch($instructionsData);
    }

    /**
     * @param \M2E\Otto\Model\Product $product
     * @param \M2E\Otto\Model\Listing\Log\Record[] $records
     *
     * @return void
     */
    private function writeLogs(Product $product, array $records): void
    {
        if (empty($records)) {
            return;
        }

        foreach ($records as $record) {
            $this->logService->addRecordToProduct(
                $record,
                $product,
                \M2E\Core\Helper\Data::INITIATOR_EXTENSION,
                \M2E\Otto\Model\Listing\Log::ACTION_CHANNEL_CHANGE,
                $this->getLogActionId(),
            );
        }
    }

    private function getLogActionId(): int
    {
        if (!empty($this->logActionId)) {
            return $this->logActionId;
        }

        return $this->logActionId = $this->logService->getNextActionId();
    }
}
