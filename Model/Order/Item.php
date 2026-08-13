<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order;

use M2E\Otto\Model\ResourceModel\Order\Item as OrderItemResource;

class Item extends \M2E\Otto\Model\ActiveRecord\AbstractModel
{
    public const IS_CANCELLATION_IN_PROGRESS_NO = 0;
    public const IS_CANCELLATION_IN_PROGRESS_YES = 1;

    private \M2E\Otto\Model\Order $order;
    private ?\M2E\Otto\Model\Magento\Product $magentoProduct = null;
    private ?\M2E\Otto\Model\Order\Item\ProxyObject $proxy = null;

    private \M2E\Otto\Model\Magento\ProductFactory $magentoProductFactory;

    // ----------------------------------------

    private \M2E\Otto\Model\Order\Item\ProxyObjectFactory $proxyObjectFactory;
    private ?\M2E\Otto\Model\Product $listingProduct = null;
    private \M2E\Core\Helper\Magento\Store $magentoStoreHelper;
    private \M2E\Otto\Model\Magento\Product\BuilderFactory $productBuilderFactory;
    private \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $magentoProductCollectionFactory;
    private \M2E\Otto\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory;
    private \M2E\Otto\Helper\Magento\Product $magentoProductHelper;
    private \M2E\Otto\Model\Otto\Order\Item\ImporterFactory $orderItemImporterFactory;
    private \M2E\Otto\Model\Order\Item\OptionsFinder $optionsFinder;
    private Repository $repository;
    private \M2E\Otto\Model\Listing\Other\Repository $otherRepository;
    private \M2E\Otto\Model\Order\Item\ProductAssignService $productAssignService;

    public function __construct(
        \M2E\Otto\Model\Order\Item\ProductAssignService $productAssignService,
        \M2E\Otto\Model\Listing\Other\Repository $otherRepository,
        Repository $repository,
        \M2E\Otto\Model\Order\Item\OptionsFinder $optionsFinder,
        \M2E\Otto\Model\Otto\Order\Item\ImporterFactory $orderItemImporterFactory,
        \M2E\Otto\Helper\Magento\Product $magentoProductHelper,
        \M2E\Otto\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\Otto\Model\Magento\Product\BuilderFactory $productBuilderFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $magentoProductCollectionFactory,
        \M2E\Core\Helper\Magento\Store $magentoStoreHelper,
        \M2E\Otto\Model\Order\Item\ProxyObjectFactory $proxyObjectFactory,
        \M2E\Otto\Model\Magento\ProductFactory $magentoProductFactory,
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
            $data,
        );
        $this->otherRepository = $otherRepository;
        $this->magentoProductFactory = $magentoProductFactory;
        $this->proxyObjectFactory = $proxyObjectFactory;
        $this->magentoStoreHelper = $magentoStoreHelper;
        $this->productBuilderFactory = $productBuilderFactory;
        $this->magentoProductCollectionFactory = $magentoProductCollectionFactory;
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
        $this->magentoProductHelper = $magentoProductHelper;
        $this->orderItemImporterFactory = $orderItemImporterFactory;
        $this->optionsFinder = $optionsFinder;
        $this->repository = $repository;
        $this->productAssignService = $productAssignService;
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init(OrderItemResource::class);
    }

    public function delete()
    {
        if ($this->isLocked()) {
            return false;
        }

        unset($this->order);

        return parent::delete();
    }

    public function getOrderId(): int
    {
        return (int)$this->getData('order_id');
    }

    public function isMappedWithMagentoProduct(): bool
    {
        return $this->getMagentoProductId() !== null;
    }

    public function getMagentoProductId(): ?int
    {
        $productId = $this->getData(OrderItemResource::COLUMN_PRODUCT_ID);
        if ($productId === null) {
            return null;
        }

        return (int)$productId;
    }

    public function setMagentoProductId(int $id): self
    {
        $this->setData(OrderItemResource::COLUMN_PRODUCT_ID, $id);

        return $this;
    }

    public function removeAssociatedWithMagentoProduct(): self
    {
        $this->setData(OrderItemResource::COLUMN_PRODUCT_ID, null);
        $this->setAssociatedProducts([]);
        $this->setAssociatedOptions([]);

        return $this;
    }

    public function getQtyReserved(): int
    {
        return (int)$this->getData('qty_reserved');
    }

    public function setAssociatedOptions(array $options): self
    {
        $this->setSetting('product_details', 'associated_options', $options);

        return $this;
    }

    public function getAssociatedOptions()
    {
        return $this->getSetting('product_details', 'associated_options', []);
    }

    public function setAssociatedProducts(array $products): Item
    {
        $this->setSetting('product_details', 'associated_products', $products);

        return $this;
    }

    public function getAssociatedProducts()
    {
        return $this->getSetting('product_details', 'associated_products', []);
    }

    public function setReservedProducts(array $products): Item
    {
        $this->setSetting('product_details', 'reserved_products', $products);

        return $this;
    }

    public function getReservedProducts()
    {
        return $this->getSetting('product_details', 'reserved_products', []);
    }

    /**
     * @param \M2E\Otto\Model\Order $order
     *
     * @return $this
     */
    public function setOrder(\M2E\Otto\Model\Order $order): self
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return \M2E\Otto\Model\Order
     */
    public function getOrder(): \M2E\Otto\Model\Order
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->order)) {
            return $this->order;
        }

        return $this->order = $this->repository->get($this->getOrderId());
    }

    public function setProduct($product): self
    {
        if (!$product instanceof \Magento\Catalog\Model\Product) {
            $this->magentoProduct = null;

            return $this;
        }

        if ($this->magentoProduct === null) {
            $this->magentoProduct = $this->magentoProductFactory->create();
        }
        $this->magentoProduct->setProduct($product);

        return $this;
    }

    /**
     * @throws \M2E\Otto\Model\Exception
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getProduct(): ?\Magento\Catalog\Model\Product
    {
        if ($this->getMagentoProductId() === null) {
            return null;
        }

        if (!$this->isMagentoProductExists()) {
            return null;
        }

        return $this->getMagentoProduct()->getProduct();
    }

    public function getMagentoProduct(): ?\M2E\Otto\Model\Magento\Product
    {
        if ($this->getMagentoProductId() === null) {
            return null;
        }

        if ($this->magentoProduct === null) {
            $this->magentoProduct = $this->magentoProductFactory->create();
            $this->magentoProduct
                ->setStoreId($this->getOrder()->getStoreId())
                ->setProductId($this->getMagentoProductId());
        }

        return $this->magentoProduct;
    }

    public function getStoreId(): int
    {
        $listingProduct = $this->getListingProduct();

        if ($listingProduct === null) {
            return $this->getOrder()->getStoreId();
        }

        $storeId = $listingProduct->getListing()->getStoreId();

        if ($storeId !== \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
            return $storeId;
        }

        if ($this->getMagentoProductId() === null) {
            return $this->magentoStoreHelper->getDefaultStoreId();
        }

        $storeIds = $this
            ->magentoProductFactory
            ->create()
            ->setProductId($this->getMagentoProductId())
            ->getStoreIds();

        if (empty($storeIds)) {
            return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }

        return (int)array_shift($storeIds);
    }

    public function getListingProduct(): ?\M2E\Otto\Model\Product
    {
        if ($this->listingProduct === null) {
            $collection = $this->listingProductCollectionFactory->create();
            $collection->addFieldToFilter('otto_product_sku', $this->getOttoProductSku());
            $listingProduct = $collection->getFirstItem();

            $this->listingProduct = $listingProduct;
        }

        return $this->listingProduct->getId() !== null ? $this->listingProduct : null;
    }

    /**
     * Associate order item with product in magento
     * @throws \M2E\Otto\Model\Exception
     * @throws \Exception
     */
    public function associateWithProduct(): void
    {
        if (
            $this->getMagentoProductId() === null
            || !$this->getMagentoProduct()->exists()
        ) {
            $this->productAssignService->assign(
                [$this],
                $this->getAssociatedProduct(),
                \M2E\Core\Helper\Data::INITIATOR_EXTENSION
            );
        }

        $supportedProductTypes = $this->magentoProductHelper->getOriginKnownTypes();

        if (!in_array($this->getMagentoProduct()->getTypeId(), $supportedProductTypes)) {
            $message = \M2E\Otto\Helper\Module\Log::encodeDescription(
                'Order Import does not support Product type: %type%.',
                [
                    'type' => $this->getMagentoProduct()->getTypeId(),
                ],
            );

            throw new \M2E\Otto\Model\Exception($message);
        }

        $this->associateVariationWithOptions();

        if (!$this->getMagentoProduct()->isStatusEnabled()) {
            throw new \M2E\Otto\Model\Exception('Product is disabled.');
        }
    }

    /**
     * Associate order item variation with options of magento product
     * @throws \LogicException
     * @throws \Exception
     */
    private function associateVariationWithOptions()
    {
        $magentoProduct = $this->getMagentoProduct();

        $existOptions = $this->getAssociatedOptions();
        $existProducts = $this->getAssociatedProducts();

        if (
            count($existProducts) == 1
            && ($magentoProduct->isDownloadableType()
                || $magentoProduct->isGroupedType()
                || $magentoProduct->isConfigurableType())
        ) {
            // grouped and configurable products can have only one associated product mapped with sold variation
            // so if count($existProducts) == 1 - there is no need for further actions
            return;
        }

        $productDetails = $this->getAssociatedProductDetails($magentoProduct);

        if (!isset($productDetails['associated_options'])) {
            return;
        }

        $existOptionsIds = array_keys($existOptions);
        $foundOptionsIds = array_keys($productDetails['associated_options']);

        if (empty($existOptions) && empty($existProducts)) {
            // options mapping invoked for the first time, use found options
            $this->setAssociatedOptions($productDetails['associated_options']);

            if (isset($productDetails['associated_products'])) {
                $this->setAssociatedProducts($productDetails['associated_products']);
            }

            $this->save();

            return;
        }

        if (!empty(array_diff($foundOptionsIds, $existOptionsIds))) {
            // options were already mapped, but not all of them
            throw new \M2E\Otto\Model\Exception\Logic('Selected Options do not match the Product Options.');
        }
    }

    /**
     * @throws \M2E\Otto\Model\Exception
     */
    private function getAssociatedProductDetails(\M2E\Otto\Model\Magento\Product $magentoProduct): array
    {
        if (!$magentoProduct->getTypeId()) {
            return [];
        }

        $magentoOptions = $this
            ->prepareMagentoOptions($magentoProduct->getVariationInstance()->getVariationsTypeRaw());

        $optionsFinder = $this->optionsFinder;
        $optionsFinder->setProduct($magentoProduct)
                      ->setMagentoOptions($magentoOptions)
                      ->addChannelOptions();

        $optionsFinder->find();

        if (!$optionsFinder->hasFailedOptions()) {
            return $optionsFinder->getOptionsData();
        }

        throw new \M2E\Otto\Model\Exception($optionsFinder->getOptionsNotFoundMessage());
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function assignProductDetails(array $associatedOptions, array $associatedProducts)
    {
        $magentoProduct = $this->magentoProductFactory->create();
        $magentoProduct->setProductId($this->getMagentoProductId());

        if (!$magentoProduct->exists()) {
            throw new \M2E\Otto\Model\Exception\Logic('Product does not exist.');
        }

        if (
            empty($associatedProducts)
            || (!$magentoProduct->isGroupedType() && empty($associatedOptions))
        ) {
            throw new \InvalidArgumentException('Required Options were not selected.');
        }

        if ($magentoProduct->isGroupedType()) {
            $associatedOptions = [];
            $associatedProducts = reset($associatedProducts);
        }

        $associatedProducts = $this->magentoProductHelper->prepareAssociatedProducts(
            $associatedProducts,
            $magentoProduct,
        );

        $this->setAssociatedProducts($associatedProducts);
        $this->setAssociatedOptions($associatedOptions);
        $this->save();
    }

    public function pretendedToBeSimple(): bool
    {
        return false;
    }

    /**
     * @return array
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getAdditionalData(): array
    {
        return $this->getSettings('additional_data');
    }

    public function isMagentoProductExists(): bool
    {
        $magentoProduct = $this->magentoProductFactory->create();
        $magentoProduct->setProductId($this->getMagentoProductId());

        return $magentoProduct->exists();
    }

    public function getProxy(): \M2E\Otto\Model\Order\Item\ProxyObject
    {
        if ($this->proxy === null) {
            $this->proxy = $this->proxyObjectFactory->create($this);
        }

        return $this->proxy;
    }

    public function getAccount(): \M2E\Otto\Model\Account
    {
        return $this->getOrder()->getAccount();
    }

    public function getOttoProductSku()
    {
        return $this->getData('otto_product_sku');
    }

    public function getTitle()
    {
        return $this->getData('title');
    }

    public function getSku(): string
    {
        return (string)$this->getData('otto_product_sku');
    }

    public function getSalePrice(): float
    {
        return (float)$this->getData('sale_price');
    }

    public function getQtyPurchased(): int
    {
        return (int)$this->getData('qty_purchased');
    }

    // ---------------------------------------

    /**
     * @return array
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getTaxDetails(): array
    {
        $taxDetails = $this->getData('tax_details');
        if (empty($taxDetails)) {
            return [];
        }

        return \M2E\Core\Helper\Json::decode($taxDetails) ?? [];
    }

    /**
     * @return float
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getTaxAmount(): float
    {
        $taxDetails = $this->getTaxDetails();

        return (float)($taxDetails['amount'] ?? 0.0);
    }

    public function isStatusUnknown(): bool
    {
        return $this->getStatus() === \M2E\Otto\Model\Order::STATUS_UNKNOWN;
    }

    public function isStatusPending(): bool
    {
        return $this->getStatus() === \M2E\Otto\Model\Order::STATUS_PENDING;
    }

    public function isStatusUnshipped(): bool
    {
        return $this->getStatus() === \M2E\Otto\Model\Order::STATUS_UNSHIPPED;
    }

    public function isStatusShipped(): bool
    {
        return $this->getStatus() === \M2E\Otto\Model\Order::STATUS_SHIPPED;
    }

    public function isStatusReturned(): bool
    {
        return $this->getStatus() === \M2E\Otto\Model\Order::STATUS_RETURNED;
    }

    public function isStatusCancelled(): bool
    {
        return $this->getStatus() === \M2E\Otto\Model\Order::STATUS_CANCELED;
    }

    public function getStatus(): int
    {
        return (int)$this->getData(OrderItemResource::COLUMN_STATUS);
    }

    public function canUpdateShippingStatus(): bool
    {
        return !$this->isStatusReturned()
            && !$this->isStatusShipped();
    }

    /**
     * @return bool
     */
    public function hasVariation(): bool
    {
        return false;
    }

    public function getTrackingDetails(): array
    {
        $trackingDetails = $this->getData('tracking_details');
        if (empty($trackingDetails)) {
            return [];
        }

        return (array)json_decode($trackingDetails, true);
    }

    public function canCreateMagentoOrder(): bool
    {
        if (
            $this->isOrdersCreationEnabled()
            && ($this->isStatusUnshipped() || $this->isStatusShipped())
        ) {
            return true;
        }

        return false;
    }

    public function isReservable(): bool
    {
        return $this->isOrdersCreationEnabled();
    }

    protected function isOrdersCreationEnabled(): bool
    {
        $listingProduct = $this->getListingProduct();

        if ($listingProduct === null) {
            return $this->getAccount()->getOrdersSettings()->isUnmanagedListingEnabled();
        }

        return $this->getAccount()->getOrdersSettings()->isListingEnabled();
    }

    /**
     * @return \Magento\Catalog\Model\Product
     * @throws \M2E\Otto\Model\Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function getAssociatedProduct(): \Magento\Catalog\Model\Product
    {
        // Item was listed by M2E
        // ---------------------------------------
        if ($this->getListingProduct() !== null) {
            return $this->getListingProduct()->getMagentoProduct()->getProduct();
        }

        // Unmanaged Item
        // ---------------------------------------
        $sku = $this->getSku();

        if (
            $sku != ''
            && strlen($sku) <= \M2E\Otto\Helper\Magento\Product::SKU_MAX_LENGTH
        ) {
            $collection = $this->magentoProductCollectionFactory->create();
            $collection->setStoreId($this->getOrder()->getAssociatedStoreId());
            $collection->addAttributeToSelect('sku');
            $collection->addAttributeToFilter('sku', $sku);

            /** @var \Magento\Catalog\Model\Product $product */
            $product = $collection->getFirstItem();

            if (!$product->isObjectNew()) {
                $this->associateWithProductEvent($product);

                return $product;
            }
        }

        // Unmanaged Item and linked
        // ---------------------------------------
        $unmanagedProduct = $this->otherRepository->getBySku($sku);

        if ($unmanagedProduct && $unmanagedProduct->getMagentoProductId() !== 0) {
            return $unmanagedProduct->getMagentoProduct()->getProduct();
        }

        $collection = $this->createProduct();
        $this->associateWithProductEvent($collection);

        return $collection;
    }

    public function prepareMagentoOptions($options): array
    {
        return \M2E\Otto\Helper\Component\Otto::prepareOptionsForOrders($options);
    }

    /**
     * @return \Magento\Catalog\Model\Product
     * @throws \M2E\Otto\Model\Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    protected function createProduct(): \Magento\Catalog\Model\Product
    {
        throw new \M2E\Otto\Model\Order\Exception\ProductCreationDisabled(
            (string)__('The product associated with this order could not be found in the Magento catalog.'),
        );
    }

    protected function associateWithProductEvent(\Magento\Catalog\Model\Product $product)
    {
        if (!$this->hasVariation()) {
            $this->_eventManager->dispatch('m2e_otto_associate_order_item_to_product', [
                'product' => $product,
                'order_item' => $this,
            ]);
        }
    }

    public function getOriginalPrice(): float
    {
        return (float)$this->getData('original_price');
    }

    public function getPlatformDiscount(): float
    {
        return (float)($this->getData('platform_discount') ?? 0.0);
    }

    public function getSellerDiscount(): float
    {
        return (float)($this->getData('seller_discount') ?? 0.0);
    }

    public function getArticleNumber(): string
    {
        return (string)$this->getData('article_number');
    }

    public function getOttoItemId(): string
    {
        return (string)$this->getData('otto_item_id');
    }

    public function getDeliveryType(): ?string
    {
        $ottoProduct = $this->getListingProduct();

        if ($ottoProduct === null) {
            return null;
        }

        return $ottoProduct->getOnlineDeliveryType();
    }

    public function isReadyForCancellation(): bool
    {
        return !$this->isStatusCancelled()
            && !$this->isStatusReturned()
            && !$this->isStatusShipped();
    }

    public function isCancellationInProgress(): bool
    {
        return (int)$this->getData(OrderItemResource::COLUMN_IS_CANCELLATION_IN_PROGRESS)
            === self::IS_CANCELLATION_IN_PROGRESS_YES;
    }

    public function setIsCancellationInProgressYes(): void
    {
        $this->setData(OrderItemResource::COLUMN_IS_CANCELLATION_IN_PROGRESS, self::IS_CANCELLATION_IN_PROGRESS_YES);
    }

    public function setIsCancellationInProgressNo(): void
    {
        $this->setData(OrderItemResource::COLUMN_IS_CANCELLATION_IN_PROGRESS, self::IS_CANCELLATION_IN_PROGRESS_NO);
    }
}
