<?php

declare(strict_types=1);

namespace M2E\Otto\Model;

use M2E\Otto\Model\Log\AbstractModel as Log;

class Order extends \M2E\Otto\Model\ActiveRecord\AbstractModel
{
    public const ADDITIONAL_DATA_KEY_IN_ORDER = 'otto_order';

    public const MAGENTO_ORDER_CREATION_FAILED_YES = 1;
    public const MAGENTO_ORDER_CREATION_FAILED_NO = 0;

    public const MAGENTO_ORDER_CREATE_MAX_TRIES = 3;

    public const STATUS_UNKNOWN = 0;
    public const STATUS_PENDING = 1;
    public const STATUS_UNSHIPPED = 2;
    public const STATUS_SHIPPED = 3;
    public const STATUS_CANCELED = 4;
    public const STATUS_RETURNED = 5;

    public const STATUS_SHIPPED_PARTIALLY = 6;
    public const STATUS_RETURNED_PARTIALLY = 7;
    public const STATUS_CANCELED_PARTIALLY = 8;

    private $statusUpdateRequired = false;
    private float $subTotalPrice;
    private ?float $grandTotalPrice = null;

    private ?\Magento\Sales\Model\Order $magentoOrder = null;
    private ?Order\ShippingAddress $shippingAddress = null;
    private ?Account $account = null;
    private ?Order\ProxyObject $proxy = null;
    private ?Order\Reserve $reserve = null;
    private ?\M2E\Otto\Model\Order\Log\Service $logService = null;
    private ?ResourceModel\Order\Item\Collection $itemsCollection = null;

    // ----------------------------------------

    private \M2E\Otto\Model\Magento\Quote\Manager $quoteManager;
    private \M2E\Otto\Model\Magento\Quote\BuilderFactory $magentoQuoteBuilderFactory;
    private \M2E\Otto\Model\Magento\Order\Updater $magentoOrderUpdater;

    private \Magento\Store\Model\StoreManager $storeManager;
    private \Magento\Sales\Model\OrderFactory $orderFactory;

    private \Magento\Framework\App\ResourceConnection $resourceConnection;

    private \Magento\Catalog\Helper\Product $productHelper;
    private \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender;
    private \M2E\Otto\Model\Order\ProxyObjectFactory $proxyObjectFactory;
    private Otto\Order\ShippingAddressFactory $shippingAddressFactory;

    private \M2E\Otto\Model\ResourceModel\Order\Change\CollectionFactory $orderChangeCollectionFactory;
    private \M2E\Otto\Model\Order\Log\ServiceFactory $orderLogServiceFactory;
    private \M2E\Otto\Model\Order\ReserveFactory $orderReserveFactory;
    private \M2E\Otto\Helper\Module\Exception $exceptionHelper;
    private \M2E\Otto\Model\ResourceModel\Order\Note\CollectionFactory $orderNoteCollectionFactory;
    private ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory;
    private \M2E\Otto\Helper\Module\Logger $loggerHelper;
    private \M2E\Otto\Helper\Data\GlobalData $globalDataHelper;
    private \M2E\Core\Helper\Magento\Store $magentoStoreHelper;
    private \M2E\Otto\Model\Account\Repository $accountRepository;
    private Order\Repository $orderRepository;
    private Order\EventDispatcher $orderEventDispatcher;
    /** @var \M2E\Otto\Model\Order\Item[] */
    private array $items;

    private \M2E\Otto\Model\Order\LogicItemCollection $logicItemCollection;
    /** @var \M2E\Otto\Model\Order\LogicItemCollectionFactory */
    private Order\LogicItemCollectionFactory $logicItemCollectionFactory;

    public function __construct(
        \M2E\Otto\Model\Order\LogicItemCollectionFactory $logicItemCollectionFactory,
        \M2E\Otto\Model\Order\Repository $orderRepository,
        \M2E\Otto\Model\Order\EventDispatcher $orderEventDispatcher,
        \M2E\Otto\Model\Account\Repository $accountRepository,
        \M2E\Otto\Model\Magento\Quote\Manager $quoteManager,
        \M2E\Otto\Model\Magento\Quote\BuilderFactory $magentoQuoteBuilderFactory,
        \M2E\Otto\Model\Magento\Order\Updater $magentoOrderUpdater,
        \M2E\Otto\Model\Order\ReserveFactory $orderReserveFactory,
        \M2E\Otto\Model\Order\Log\ServiceFactory $orderLogServiceFactory,
        \M2E\Otto\Model\Order\ProxyObjectFactory $proxyObjectFactory,
        \M2E\Otto\Model\Otto\Order\ShippingAddressFactory $shippingAddressFactory,
        \M2E\Otto\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \M2E\Otto\Model\ResourceModel\Order\Note\CollectionFactory $orderNoteCollectionFactory,
        \M2E\Otto\Model\ResourceModel\Order\Change\CollectionFactory $orderChangeCollectionFactory,
        \M2E\Core\Helper\Magento\Store $magentoStoreHelper,
        \M2E\Otto\Helper\Data\GlobalData $globalDataHelper,
        \M2E\Otto\Helper\Module\Logger $loggerHelper,
        \M2E\Otto\Helper\Module\Exception $exceptionHelper,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );

        $this->orderRepository = $orderRepository;
        $this->orderEventDispatcher = $orderEventDispatcher;
        $this->storeManager = $storeManager;
        $this->orderFactory = $orderFactory;
        $this->resourceConnection = $resourceConnection;
        $this->productHelper = $productHelper;
        $this->quoteManager = $quoteManager;
        $this->orderSender = $orderSender;
        $this->proxyObjectFactory = $proxyObjectFactory;
        $this->shippingAddressFactory = $shippingAddressFactory;
        $this->orderChangeCollectionFactory = $orderChangeCollectionFactory;
        $this->orderLogServiceFactory = $orderLogServiceFactory;
        $this->orderReserveFactory = $orderReserveFactory;
        $this->exceptionHelper = $exceptionHelper;
        $this->orderNoteCollectionFactory = $orderNoteCollectionFactory;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->loggerHelper = $loggerHelper;
        $this->globalDataHelper = $globalDataHelper;
        $this->magentoStoreHelper = $magentoStoreHelper;
        $this->magentoQuoteBuilderFactory = $magentoQuoteBuilderFactory;
        $this->magentoOrderUpdater = $magentoOrderUpdater;
        $this->accountRepository = $accountRepository;
        $this->logicItemCollectionFactory = $logicItemCollectionFactory;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(\M2E\Otto\Model\ResourceModel\Order::class);
    }

    public function delete()
    {
        if ($this->isLocked()) {
            return false;
        }

        $orderNoteCollection = $this->orderNoteCollectionFactory->create();
        $orderNoteCollection->addFieldToFilter('order_id', $this->getId());
        foreach ($orderNoteCollection->getItems() as $orderNote) {
            $orderNote->delete();
        }

        foreach ($this->getItemsCollection()->getItems() as $item) {
            $item->delete();
        }

        $orderChangeCollection = $this->orderChangeCollectionFactory->create();
        $orderChangeCollection->addFieldToFilter('order_id', $this->getId());
        foreach ($orderChangeCollection->getItems() as $orderChange) {
            $orderChange->delete();
        }

        $this->account = null;
        $this->magentoOrder = null;
        $this->itemsCollection = null;
        $this->proxy = null;

        return parent::delete();
    }

    public function getId(): ?int
    {
        $orderId = parent::getId();
        if ($orderId === null) {
            return null;
        }

        return $orderId;
    }

    public function findItem(int $id): ?\M2E\Otto\Model\Order\Item
    {
        foreach ($this->getItems() as $item) {
            if ($id === $item->getId()) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @return \M2E\Otto\Model\Order\Item[]
     */
    public function getItems(): array
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->items)) {
            return $this->items;
        }

        return $this->items = $this->orderRepository->findItemsByOrder($this);
    }

    /**
     * @deprecated
     */
    public function getItemsCollection(): \M2E\Otto\Model\ResourceModel\Order\Item\Collection
    {
        if ($this->itemsCollection === null) {
            $this->itemsCollection = $this->orderItemCollectionFactory->create();
            $this->itemsCollection->addFieldToFilter('order_id', $this->getId());

            foreach ($this->itemsCollection->getItems() as $orderItem) {
                $orderItem->setOrder($this);
            }
        }

        return $this->itemsCollection;
    }

    public function getLogicItemsCollection(): \M2E\Otto\Model\Order\LogicItemCollection
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->logicItemCollection)) {
            $this->logicItemCollection = $this->logicItemCollectionFactory->createFromOrder($this);
        }

        return $this->logicItemCollection;
    }

    public function getMagentoOrderCreationLatestAttemptDate()
    {
        return $this->getData('magento_order_creation_latest_attempt_date');
    }

    public function getCreateDate()
    {
        return $this->getData('create_date');
    }

    public function getReservationState(): int
    {
        return (int)$this->getData('reservation_state');
    }

    public function getReservationStartDate(): string
    {
        return (string)$this->getData('reservation_start_date');
    }

    /**
     * Check whether the order has items, listed by M2E (also true for linked Unmanaged listings)
     */
    public function hasListingProductItems(): bool
    {
        return $this->getListingProducts() !== [];
    }

    /**
     * @return \M2E\Otto\Model\Product[]
     */
    public function getListingProducts(): array
    {
        $listingProducts = [];
        foreach ($this->getItemsCollection()->getItems() as $item) {
            $listingProduct = $item->getListingProduct();

            if ($listingProduct === null) {
                continue;
            }

            $listingProducts[] = $listingProduct;
        }

        return $listingProducts;
    }

    /**
     * Check whether the order has items, listed by Unmanaged software
     */
    public function hasOtherListingItems(): bool
    {
        return count($this->getListingProducts()) != $this->getItemsCollection()->getSize();
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function isMagentoShipmentCreatedByOrder(\Magento\Sales\Model\Order\Shipment $magentoShipment): bool
    {
        $additionalData = $this->getAdditionalData();
        if (empty($additionalData['created_shipments_ids']) || !is_array($additionalData['created_shipments_ids'])) {
            return false;
        }

        return in_array($magentoShipment->getId(), $additionalData['created_shipments_ids']);
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getAdditionalData(): array
    {
        return $this->getSettings('additional_data');
    }

    //########################################

    public function canCreateMagentoOrder(): bool
    {
        if ($this->hasMagentoOrder()) {
            return false;
        }

        if ($this->isCanceled()) {
            return false;
        }

        if ($this->isStatusPending()) {
            return false;
        }

        $itemResults = [];
        foreach ($this->getLogicItemsCollection()->getAllowedForCreateInMagento() as $logicItem) {
            foreach ($logicItem->getItemsAllowedForCreateInMagento() as $orderItem) {
                $itemResults[] = $orderItem->canCreateMagentoOrder();
            }
        }

        if (empty($itemResults)) {
            return false;
        }

        // All order item (except items in status Cancelled and Returned)
        // must be in status Unshipped or Shipped
        if (in_array(false, $itemResults, true)) {
            return false;
        }

        return true;
    }

    //########################################

    public function hasMagentoOrder(): bool
    {
        return $this->getMagentoOrderId() !== null;
    }

    public function getMagentoOrderId()
    {
        return $this->getData('magento_order_id');
    }

    public function getOttoOrderId(): string
    {
        return (string)$this->getData(\M2E\Otto\Model\ResourceModel\Order::COLUMN_OTTO_ORDER_ID);
    }

    public function getOttoOrderNumber(): string
    {
        return (string)$this->getData(\M2E\Otto\Model\ResourceModel\Order::COLUMN_OTTO_ORDER_NUMBER);
    }

    // ---------------------------------------

    public function isCanceled(): bool
    {
        return $this->getOrderStatus() === self::STATUS_CANCELED;
    }

    // ---------------------------------------

    public function getOrderStatus(): int
    {
        return (int)($this->getData('order_status') ?? 0);
    }

    // ---------------------------------------

    public function isStatusPending(): bool
    {
        return $this->getOrderStatus() === self::STATUS_PENDING;
    }

    public function isStatusCanceled(): bool
    {
        return $this->getOrderStatus() === self::STATUS_CANCELED;
    }

    // ---------------------------------------

    /**
     * @throws \Throwable
     * @throws \M2E\Otto\Model\Exception\Logic
     * @throws \M2E\Otto\Model\Magento\Quote\FailDuringEventProcessing
     * @throws \M2E\Otto\Model\Order\Exception\ProductCreationDisabled
     * @throws \M2E\Otto\Model\Exception
     */
    public function createMagentoOrder($canCreateExistOrder = false)
    {
        try {
            // Check if we are wrapped by an another MySql transaction
            // ---------------------------------------
            $connection = $this->resourceConnection->getConnection();
            if ($transactionLevel = $connection->getTransactionLevel()) {
                $this->loggerHelper->process(
                    ['transaction_level' => $transactionLevel],
                    'MySql Transaction Level Problem'
                );

                while ($connection->getTransactionLevel()) {
                    $connection->rollBack();
                }
            }
            // ---------------------------------------

            /**
             *  Since version 2.1.8 Magento added check if product is saleable before creating quote.
             *  When order is creating from back-end, this check is skipped. See example at
             *  Magento\Sales\Controller\Adminhtml\Order\Create.php
             */
            $this->productHelper->setSkipSaleableCheck(true);

            // Store must be initialized before products
            // ---------------------------------------
            $this->associateWithStore();
            $this->associateItemsWithProducts();
            // ---------------------------------------

            $this->beforeCreateMagentoOrder($canCreateExistOrder);

            // Create magento order
            // ---------------------------------------
            $proxyOrder = $this->getProxy();
            $proxyOrder->setStore($this->getStore());

            $magentoQuoteBuilder = $this->magentoQuoteBuilderFactory->create($proxyOrder);
            $magentoQuote = $magentoQuoteBuilder->build();

            $this->globalDataHelper->unsetValue(self::ADDITIONAL_DATA_KEY_IN_ORDER);
            $this->globalDataHelper->setValue(self::ADDITIONAL_DATA_KEY_IN_ORDER, $this);

            try {
                $this->magentoOrder = $this->quoteManager->submit($magentoQuote);
            } catch (\M2E\Otto\Model\Magento\Quote\FailDuringEventProcessing $e) {
                $this->addWarningLog(
                    'Magento Order was created.
                     However one or more post-processing actions on Magento Order failed.
                     This may lead to some issues in the future.
                     Please check the configuration of the ancillary services of your Magento.
                     For more details, read the original Magento warning: %msg%.',
                    [
                        'msg' => $e->getMessage(),
                    ]
                );
                $this->magentoOrder = $e->getOrder();
            }

            $magentoOrderId = $this->getMagentoOrderId();

            if (empty($magentoOrderId)) {
                $now = \M2E\Core\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s');
                $this->addData([
                    'magento_order_id' => $this->magentoOrder->getId(),
                    'magento_order_creation_failure' => self::MAGENTO_ORDER_CREATION_FAILED_NO,
                    'magento_order_creation_latest_attempt_date' => $now,
                ]);

                $this->setMagentoOrder($this->magentoOrder);
                $this->save();
            }

            $this->afterCreateMagentoOrder();
            unset($magentoQuoteBuilder);
        } catch (\Throwable $exception) {
            unset($magentoQuoteBuilder);
            $this->globalDataHelper->unsetValue(self::ADDITIONAL_DATA_KEY_IN_ORDER);

            /**
             * \Magento\CatalogInventory\Model\StockManagement::registerProductsSale()
             * could open an transaction and may does not
             * close it in case of Exception. So all the next changes may be lost.
             */
            $connection = $this->resourceConnection->getConnection();
            if ($transactionLevel = $connection->getTransactionLevel()) {
                $this->loggerHelper->process(
                    [
                        'transaction_level' => $transactionLevel,
                        'error' => $exception->getMessage(),
                        'trace' => $exception->getTraceAsString(),
                    ],
                    'MySql Transaction Level Problem'
                );

                while ($connection->getTransactionLevel()) {
                    $connection->rollBack();
                }
            }

            $this->_eventManager->dispatch('m2e_otto_order_place_failure', ['order' => $this]);

            $now = \M2E\Core\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s');
            $this->addData([
                'magento_order_creation_failure' => self::MAGENTO_ORDER_CREATION_FAILED_YES,
                'magento_order_creation_fails_count' => $this->getMagentoOrderCreationFailsCount() + 1,
                'magento_order_creation_latest_attempt_date' => $now,
            ]);
            $this->save();

            $message = 'Magento Order was not created. Reason: %msg%';
            if ($exception instanceof \M2E\Otto\Model\Order\Exception\ProductCreationDisabled) {
                $this->addInfoLog($message, ['msg' => $exception->getMessage()], [], true);
            } else {
                $this->exceptionHelper->process($exception);
                $this->addErrorLog($message, ['msg' => $exception->getMessage()]);
            }

            if ($this->isReservable()) {
                $this->getReserve()->place();
            }

            throw $exception;
        }
    }

    // ---------------------------------------

    /**
     * Find the store, where order should be placed
     * @throws \M2E\Otto\Model\Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function associateWithStore()
    {
        $storeId = $this->hasStoreId() ? $this->getStoreId() : $this->getAssociatedStoreId();
        $store = $this->storeManager->getStore($storeId);

        if ($store->getId() === null) {
            throw new \M2E\Otto\Model\Exception('Store does not exist.');
        }

        if (
            !$this->hasStoreId() ||
            $this->getStoreId() !== (int)$store->getId()
        ) {
            $this->setStoreId((int)$store->getId())
                 ->save();
        }

        if (!$store->getConfig('payment/ottopayment/active')) {
            throw new \M2E\Otto\Model\Exception(
                strtr(
                    'Payment method ":extension_title Payment" is disabled under
                <i>Stores > Settings > Configuration > Sales > Payment Methods > :extension_title Payment.</i>',
                    [
                        ':extension_title' => \M2E\Otto\Helper\Module::getExtensionTitle(),
                    ]
                )
            );
        }

        if (!$store->getConfig('carriers/ottoshipping/active')) {
            throw new \M2E\Otto\Model\Exception(
                strtr(
                    'Shipping method ":extension_title Shipping" is disabled under
                <i>Stores > Settings > Configuration > Sales > Shipping Methods > :extension_title Shipping.</i>',
                    [
                        ':extension_title' => \M2E\Otto\Helper\Module::getExtensionTitle(),
                    ]
                )
            );
        }
    }

    public function hasStoreId(): bool
    {
        return $this->getData('store_id') !== null;
    }

    public function setStoreId(int $storeId): self
    {
        $this->setData('store_id', $storeId);

        return $this;
    }

    public function getStoreId(): int
    {
        return (int)$this->getData('store_id');
    }

    //########################################

    public function getAssociatedStoreId(): int
    {
        $productVariantSkus = $this->getListingProducts();

        if (empty($productVariantSkus)) {
            $storeId = $this->getAccount()->getOrdersSettings()->getUnmanagedListingStoreId();
        } elseif ($this->getAccount()->getOrdersSettings()->isListingStoreModeCustom()) {
            $storeId = $this->getAccount()->getOrdersSettings()->getListingStoreIdForCustomMode();
        } else {
            $firstProductVariantSku = reset($productVariantSkus);
            $storeId = $firstProductVariantSku->getListing()->getStoreId();
        }

        if ($storeId === 0) {
            $storeId = $this->magentoStoreHelper->getDefaultStoreId();
        }

        return $storeId;
    }

    /**
     * @return \M2E\Otto\Model\Account
     */
    public function getAccount(): Account
    {
        if ($this->account === null) {
            $this->account = $this->accountRepository->get($this->getAccountId());
        }

        return $this->account;
    }

    public function setAccount(\M2E\Otto\Model\Account $account): self
    {
        $this->account = $account;

        return $this;
    }

    public function getAccountId(): int
    {
        return (int)$this->getData('account_id');
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore(): \Magento\Store\Api\Data\StoreInterface
    {
        return $this->storeManager->getStore($this->getStoreId());
    }

    /**
     * Associate each order item with product in magento
     */
    public function associateItemsWithProducts(): void
    {
        foreach ($this->getLogicItemsCollection()->getAllowedForCreateInMagento() as $logicItem) {
            foreach ($logicItem->getItemsAllowedForCreateInMagento() as $item) {
                $item->associateWithProduct();
            }
        }
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     * @throws \M2E\Otto\Model\Exception
     */
    private function beforeCreateMagentoOrder($canCreateExistOrder)
    {
        if ($this->getMagentoOrderId() !== null && !$canCreateExistOrder) {
            throw new \M2E\Otto\Model\Exception('Magento Order is already created.');
        }

        $reserve = $this->getReserve();

        if ($reserve->isPlaced()) {
            $reserve->setFlag('order_reservation', true);
            $reserve->release();
        }
    }

    //########################################

    public function getBuyerName()
    {
        return $this->getData('buyer_name');
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getShippingDetails(): array
    {
        return $this->getSettings('shipping_details');
    }

    public function getReserve(): ?\M2E\Otto\Model\Order\Reserve
    {
        if ($this->reserve === null) {
            $this->reserve = $this->orderReserveFactory->create($this);
        }

        return $this->reserve;
    }

    //########################################

    public function getProxy(): Order\ProxyObject
    {
        if ($this->proxy === null) {
            $this->proxy = $this->proxyObjectFactory->create($this);
        }

        return $this->proxy;
    }

    //########################################

    /**
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function addWarningLog(
        $description,
        array $params = [],
        array $links = [],
        $isUnique = false,
        $additionalData = []
    ): bool {
        return $this->addLog(
            $description,
            Log::TYPE_WARNING,
            $params,
            $links,
            $isUnique,
            $additionalData
        );
    }

    //########################################

    /**
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function addLog(
        $description,
        $type,
        array $params = [],
        array $links = [],
        $isUnique = false,
        $additionalData = []
    ): bool {
        $log = $this->getLogService();

        if (!empty($params)) {
            $description = \M2E\Otto\Helper\Module\Log::encodeDescription($description, $params, $links);
        }

        return $log->addMessage(
            $this,
            $description,
            $type,
            $additionalData,
            $isUnique
        );
    }

    //########################################

    public function getLogService(): \M2E\Otto\Model\Order\Log\Service
    {
        if (!$this->logService) {
            $this->logService = $this->orderLogServiceFactory->create();
        }

        return $this->logService;
    }

    /**
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function afterCreateMagentoOrder()
    {
        // add history comments
        // ---------------------------------------
        $magentoOrderUpdater = $this->magentoOrderUpdater;
        $magentoOrderUpdater->setMagentoOrder($this->getMagentoOrder());
        $magentoOrderUpdater->updateComments($this->getProxy()->getComments());
        $magentoOrderUpdater->finishUpdate();
        // ---------------------------------------

        $this->orderEventDispatcher->dispatchEventsMagentoOrderCreated($this);

        $this->addSuccessLog('Magento Order #%order_id% was created.', [
            '!order_id' => $this->getMagentoOrder()->getRealOrderId(),
        ]);

        if ($this->getAccount()->getOrdersSettings()->isCustomerNewNotifyWhenOrderCreated()) {
            $this->orderSender->send($this->getMagentoOrder());
        }
    }

    public function getMagentoOrder(): ?\Magento\Sales\Model\Order
    {
        if (!$this->hasMagentoOrder()) {
            return null;
        }

        if ($this->magentoOrder === null) {
            $this->magentoOrder = $this->orderFactory->create()->load($this->getMagentoOrderId());
        }

        return $this->magentoOrder->getId() !== null ? $this->magentoOrder : null;
    }

    public function setMagentoOrder(\Magento\Sales\Model\Order $order): self
    {
        $this->magentoOrder = $order;

        return $this;
    }

    //########################################

    /**
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function addSuccessLog(
        $description,
        array $params = [],
        array $links = [],
        $isUnique = false,
        $additionalData = []
    ): bool {
        return $this->addLog(
            $description,
            Log::TYPE_SUCCESS,
            $params,
            $links,
            $isUnique,
            $additionalData
        );
    }

    public function getMagentoOrderCreationFailsCount(): int
    {
        return (int)$this->getData('magento_order_creation_fails_count');
    }

    //########################################

    /**
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function addInfoLog(
        $description,
        array $params = [],
        array $links = [],
        $isUnique = false,
        $additionalData = []
    ): bool {
        return $this->addLog(
            $description,
            Log::TYPE_INFO,
            $params,
            $links,
            $isUnique,
            $additionalData
        );
    }

    //########################################

    /**
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function addErrorLog(
        $description,
        array $params = [],
        array $links = [],
        $isUnique = false,
        $additionalData = []
    ): bool {
        return $this->addLog(
            $description,
            Log::TYPE_ERROR,
            $params,
            $links,
            $isUnique,
            $additionalData
        );
    }

    public function isReservable(): bool
    {
        if ($this->getMagentoOrderId() !== null) {
            return false;
        }

        if ($this->getReserve()->isPlaced()) {
            return false;
        }

        if ($this->isCanceled()) {
            return false;
        }

        foreach ($this->getItemsCollection()->getItems() as $item) {
            if (!$item->isReservable()) {
                return false;
            }
        }

        return true;
    }

    public function isStatusUnshipping(): bool
    {
        return $this->getOrderStatus() === self::STATUS_UNSHIPPED;
    }

    public function isStatusShipping(): bool
    {
        return $this->getOrderStatus() === self::STATUS_SHIPPED;
    }

    public function addCreatedMagentoShipment(\Magento\Sales\Model\Order\Shipment $magentoShipment): self
    {
        $additionalData = $this->getAdditionalData();
        $additionalData['created_shipments_ids'][] = $magentoShipment->getId();
        $this->setSettings('additional_data', $additionalData);

        return $this;
    }

    public function getBuyerEmail()
    {
        return $this->getData('buyer_email');
    }

    public function getCurrency()
    {
        return $this->getData('currency');
    }

    public function getPaidAmount()
    {
        return $this->getData('paid_amount');
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getTaxRate(): float
    {
        $taxDetails = $this->getTaxDetails();
        if (empty($taxDetails)) {
            return 0.0;
        }

        return (float)$taxDetails['rate'];
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getTaxDetails(): array
    {
        return $this->getSettings('tax_details');
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getTaxAmount(): float
    {
        $taxDetails = $this->getTaxDetails();
        if (empty($taxDetails)) {
            return 0.0;
        }

        return (float)($taxDetails['amount'] ?? 0.0);
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function isShippingPriceHasTax(): bool
    {
        if (!$this->hasShippingTax()) {
            return false;
        }

        if ($this->isVatTax()) {
            return true;
        }

        $taxDetails = $this->getTaxDetails();

        return isset($taxDetails['includes_shipping']) && $taxDetails['includes_shipping'];
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function hasShippingTax(): bool
    {
        return $this->getShippingTax() > 0;
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getShippingTax()
    {
        $taxDetails = $this->getTaxDetails();

        return $taxDetails['shipping_fee_tax'] ?? 0.0;
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function isVatTax(): bool
    {
        if (!$this->hasTax()) {
            return false;
        }

        $taxDetails = $this->getTaxDetails();

        return $taxDetails['is_vat'];
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function hasTax(): bool
    {
        $taxDetails = $this->getTaxDetails();

        return !empty($taxDetails['rate']);
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function isSalesTax(): bool
    {
        if (!$this->hasTax()) {
            return false;
        }

        $taxDetails = $this->getTaxDetails();

        return !$taxDetails['is_vat'];
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getShippingService(): string
    {
        $shippingDetails = $this->getShippingDetails();

        return $shippingDetails['service'] ?? '';
    }

    public function getShippingAdditionalInfo(): string
    {
        $shippingDetails = $this->getShippingDetails();

        return $shippingDetails['additional_info'] ?? '';
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getShippingDate(): string
    {
        $shippingDetails = $this->getShippingDetails();

        return $shippingDetails['date'] ?? '';
    }

    public function getShippingDateTo()
    {
        return $this->getData('shipping_date_to');
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getShippingAddress(): \M2E\Otto\Model\Order\ShippingAddress
    {
        if ($this->shippingAddress === null) {
            $shippingDetails = $this->getShippingDetails();
            $address = $shippingDetails['address'] ?? [];

            return $this
                ->shippingAddressFactory
                ->create($this)
                ->setData($address);
        }

        return $this->shippingAddress;
    }

    public function getPaymentMethod(): string
    {
        return $this->getData('payment_method_name') ?? '';
    }

    public function getPaymentDate(): string
    {
        return $this->getData('payment_date') ?? '';
    }

    public function getPurchaseUpdateDate()
    {
        return $this->getData('purchase_update_date');
    }

    public function getPurchaseCreateDate()
    {
        return $this->getData('purchase_create_date');
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getGrandTotalPrice(): ?float
    {
        if ($this->grandTotalPrice === null) {
            $this->grandTotalPrice = $this->getSubtotalPrice();
            $this->grandTotalPrice += round($this->getShippingPrice(), 2);
        }

        return $this->grandTotalPrice;
    }

    public function getStatusForMagentoOrder(): string
    {
        if ($this->isStatusUnshipping()) {
            return $this->getAccount()->getOrdersSettings()->getStatusMappingForProcessing();
        }

        if ($this->isStatusShipping()) {
            return $this->getAccount()->getOrdersSettings()->getStatusMappingForProcessingShipped();
        }

        return '';
    }

    public function getSubtotalPrice(): float
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->subTotalPrice)) {
            $subtotal = 0;

            foreach ($this->getLogicItemsCollection()->getAll() as $logicItem) {
                $subtotal += $logicItem->getSubtotalPrice();
            }

            $this->subTotalPrice = $subtotal;
        }

        return $this->subTotalPrice;
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getShippingPrice(): float
    {
        $shippingDetails = $this->getShippingDetails();

        return (float)($shippingDetails['price'] ?? 0.0);
    }

    public function getShippingTrackingDetails(): array
    {
        $trackingDetails = [];

        $existedTrackingNumbers = [];
        foreach ($this->getItems() as $item) {
            $itemTrackingDetails = $item->getTrackingDetails();
            if (empty($itemTrackingDetails)) {
                continue;
            }

            $trackNumber = $itemTrackingDetails['tracking_number'] ?? null;
            if (empty($trackNumber)) {
                continue;
            }

            if (isset($trackingDetails[$trackNumber])) {
                $trackingDetails[$trackNumber]['order_items'][] = $item;
                continue;
            }

            if (isset($existedTrackingNumbers[$trackNumber])) {
                continue;
            }

            $trackingDetails[$trackNumber] = [
                'tracking_number' => $itemTrackingDetails['tracking_number'],
                'shipping_carrier' => $itemTrackingDetails['shipping_carrier'],
                'shipping_carrier_service_code' => $itemTrackingDetails['shipping_carrier_service_code'],
                'order_items' => [$item],
            ];

            $existedTrackingNumbers[$trackNumber] = true;
        }

        return $trackingDetails;
    }

    public function canUpdatePaymentStatus(): bool
    {
        if ($this->isStatusPending()) {
            return false;
        }

        return true;
    }

    public function canUpdateShippingStatus(): bool
    {
        if (
            $this->isStatusPending()
            || $this->isStatusShipping()
            || $this->isStatusCanceled()
        ) {
            return false;
        }

        return true;
    }

    public function markAsStatusUpdateRequired(): self
    {
        $this->statusUpdateRequired = true;

        return $this;
    }

    public function getStatusUpdateRequired(): bool
    {
        return $this->statusUpdateRequired;
    }

    public function canCancel(): bool
    {
        if ($this->isCanceled()) {
            return false;
        }

        return true;
    }
}
