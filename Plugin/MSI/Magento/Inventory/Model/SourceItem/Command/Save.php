<?php

namespace M2E\Otto\Plugin\MSI\Magento\Inventory\Model\SourceItem\Command;

use Magento\InventoryApi\Api\Data\SourceItemInterface;

class Save extends \M2E\Otto\Plugin\AbstractPlugin
{
    private \M2E\Otto\Model\MSI\AffectedProducts $msiAffectedProducts;
    private \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder;
    private \Magento\Inventory\Model\SourceItemRepository $sourceItemRepo;
    private \M2E\Otto\Model\Magento\Product\ChangeAttributeTrackerFactory $changeAttributeTrackerFactory;
    private \M2E\Otto\Model\Listing\LogService $listingLogService;

    public function __construct(
        \M2E\Otto\Model\Listing\LogService $listingLogService,
        \M2E\Otto\Model\MSI\AffectedProducts $msiAffectedProducts,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \M2E\Otto\Model\Magento\Product\ChangeAttributeTrackerFactory $changeAttributeTrackerFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->msiAffectedProducts = $msiAffectedProducts;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->changeAttributeTrackerFactory = $changeAttributeTrackerFactory;
        $this->listingLogService = $listingLogService;

        $this->sourceItemRepo = $objectManager->get(\Magento\Inventory\Model\SourceItemRepository::class);
    }

    public function aroundExecute($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('execute', $interceptor, $callback, $arguments);
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    protected function processExecute($interceptor, \Closure $callback, array $arguments)
    {
        /** @var \Magento\InventoryApi\Api\Data\SourceItemInterface[] $sourceItems */
        $sourceItems = $arguments[0];
        $sourceItemsBefore = [];

        foreach ($sourceItems as $sourceItem) {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(SourceItemInterface::SOURCE_CODE, $sourceItem->getSourceCode())
                ->addFilter(SourceItemInterface::SKU, $sourceItem->getSku())
                ->create();

            foreach ($this->sourceItemRepo->getList($searchCriteria)->getItems() as $beforeSourceItem) {
                $sourceItemsBefore[$sourceItem->getSourceCode()] = $beforeSourceItem;
            }
        }

        $result = $callback(...$arguments);

        foreach ($sourceItems as $sourceItem) {
            $sourceItemBefore = $sourceItemsBefore[$sourceItem->getSourceCode()] ?? null;
            $affected = $this->msiAffectedProducts->getAffectedProductsBySourceAndSku(
                $sourceItem->getSourceCode(),
                $sourceItem->getSku()
            );

            if (empty($affected)) {
                continue;
            }

            $this->addListingProductInstructions($affected);

            $this->processQty($sourceItemBefore, $sourceItem, $affected);
            $this->processStockAvailability($sourceItemBefore, $sourceItem, $affected);
        }

        return $result;
    }

    /**
     * @param \Magento\InventoryApi\Api\Data\SourceItemInterface|null $beforeSourceItem
     * @param \Magento\InventoryApi\Api\Data\SourceItemInterface $afterSourceItem
     * @param \M2E\Otto\Model\Product[] $affectedProducts
     */
    private function processQty($beforeSourceItem, $afterSourceItem, $affectedProducts)
    {
        $oldValue = $beforeSourceItem !== null ? $beforeSourceItem->getQuantity() : 'undefined';
        $newValue = $afterSourceItem->getQuantity();

        if ($oldValue == $newValue) {
            return;
        }

        foreach ($affectedProducts as $listingProduct) {
            $this->logListingProductMessage(
                $listingProduct,
                $afterSourceItem,
                \M2E\Otto\Model\Listing\Log::ACTION_CHANGE_PRODUCT_QTY,
                $oldValue,
                $newValue
            );
        }
    }

    /**
     * @param \Magento\InventoryApi\Api\Data\SourceItemInterface|null $beforeSourceItem
     * @param \Magento\InventoryApi\Api\Data\SourceItemInterface $afterSourceItem
     * @param \M2E\Otto\Model\Product[] $affectedProducts
     */
    private function processStockAvailability($beforeSourceItem, $afterSourceItem, $affectedProducts)
    {
        $oldValue = 'undefined';
        $beforeSourceItem !== null && $oldValue = $beforeSourceItem->getStatus() ? 'IN Stock' : 'OUT of Stock';
        $newValue = $afterSourceItem->getStatus() ? 'IN Stock' : 'OUT of Stock';

        if ($oldValue == $newValue) {
            return;
        }

        foreach ($affectedProducts as $listingProduct) {
            $this->logListingProductMessage(
                $listingProduct,
                $afterSourceItem,
                \M2E\Otto\Model\Listing\Log::ACTION_CHANGE_PRODUCT_STOCK_AVAILABILITY,
                $oldValue,
                $newValue
            );
        }
    }

    private function logListingProductMessage(
        \M2E\Otto\Model\Product $listingProduct,
        \Magento\InventoryApi\Api\Data\SourceItemInterface $sourceItem,
        $action,
        $oldValue,
        $newValue
    ): void {
        $this->listingLogService->addProduct(
            $listingProduct,
            \M2E\Core\Helper\Data::INITIATOR_EXTENSION,
            $action,
            null,
            \M2E\Otto\Helper\Module\Log::encodeDescription(
                'Value was changed from [%from%] to [%to%] in the "%source%" Source.',
                ['!from' => $oldValue, '!to' => $newValue, '!source' => $sourceItem->getSourceCode()]
            ),
            \M2E\Otto\Model\Log\AbstractModel::TYPE_INFO
        );
    }

    /**
     * @param \M2E\Otto\Model\Product[] $affectedProducts
     */
    private function addListingProductInstructions(array $affectedProducts): void
    {
        foreach ($affectedProducts as $listingProduct) {
            $changeAttributeTracker = $this->changeAttributeTrackerFactory->create(
                $listingProduct,
                $listingProduct->getDescriptionTemplate()
            );
            $changeAttributeTracker->addInstructionWithPotentiallyChangedType();
            $changeAttributeTracker->flushInstructions();
        }
    }
}
