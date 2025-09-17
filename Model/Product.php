<?php

declare(strict_types=1);

namespace M2E\Otto\Model;

use M2E\Core\Model\Connector\Response\MessageCollection;
use M2E\Otto\Model\ResourceModel\Product as ListingProductResource;

/**
 * @method \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator getActionConfigurator()
 * @method setActionConfigurator(\M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $configurator)
 */
class Product extends \M2E\Otto\Model\ActiveRecord\AbstractModel implements
    \M2E\Otto\Model\ProductInterface
{
    public const ACTION_LIST = 1;
    public const ACTION_RELIST = 2;
    public const ACTION_REVISE = 3;
    public const ACTION_STOP = 4;
    public const ACTION_DELETE = 5;

    public const STATUS_NOT_LISTED = 0;
    public const STATUS_LISTED = 2;
    public const STATUS_INACTIVE = 8;

    public const STATUS_CHANGER_UNKNOWN = 0;
    public const STATUS_CHANGER_SYNCH = 1;
    public const STATUS_CHANGER_USER = 2;
    public const STATUS_CHANGER_COMPONENT = 3;
    public const STATUS_CHANGER_OBSERVER = 4;

    public const MOVING_LISTING_OTHER_SOURCE_KEY = 'moved_from_listing_other_id';

    public const GROUPED_PRODUCT_MODE_OPTIONS = 0;
    public const GROUPED_PRODUCT_MODE_SET = 1;

    public const INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED = 'channel_status_changed';
    public const INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED = 'channel_qty_changed';
    public const INSTRUCTION_TYPE_CHANNEL_PRICE_CHANGED = 'channel_price_changed';
    public const INSTRUCTION_TYPE_CHANNEL_MOIN_CHANGED = 'channel_moin_changed';
    public const INSTRUCTION_TYPE_CHANNEL_PRODUCT_URL_CHANGED = 'channel_product_url_changed';
    public const INSTRUCTION_TYPE_CHANNEL_SHIPPING_PROFILE_ID_CHANGED = 'channel_shipping_profile_id_changed';

    private \M2E\Otto\Model\Listing $listing;
    private \M2E\Otto\Model\Category $category;
    private \M2E\Otto\Model\Product\DataProvider $dataProvider;
    private \M2E\Otto\Model\Magento\Product\Cache $magentoProductModel;
    private \M2E\Otto\Model\Listing\Repository $listingRepository;
    private \M2E\Otto\Model\Magento\Product\CacheFactory $magentoProductFactory;
    private \M2E\Otto\Model\Otto\Listing\Product\Description\RendererFactory $descriptionRendererFactory;
    private \M2E\Otto\Model\Policy\ShippingDataProviderFactory $shippingDataProviderFactory;
    private \M2E\Otto\Model\Category\Repository $categoryRepository;
    private \M2E\Otto\Model\Product\DataProviderFactory $dataProviderFactory;

    public function __construct(
        \M2E\Otto\Model\Product\DataProviderFactory $dataProviderFactory,
        \M2E\Otto\Model\Category\Repository $categoryRepository,
        \M2E\Otto\Model\Otto\Listing\Product\Description\RendererFactory $descriptionRendererFactory,
        \M2E\Otto\Model\Listing\Repository $listingRepository,
        \M2E\Otto\Model\Magento\Product\CacheFactory $magentoProductFactory,
        \M2E\Otto\Model\Policy\ShippingDataProviderFactory $shippingDataProviderFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context, $registry);

        $this->descriptionRendererFactory = $descriptionRendererFactory;
        $this->listingRepository = $listingRepository;
        $this->magentoProductFactory = $magentoProductFactory;
        $this->shippingDataProviderFactory = $shippingDataProviderFactory;
        $this->categoryRepository = $categoryRepository;
        $this->dataProviderFactory = $dataProviderFactory;
    }

    protected function _construct(): void
    {
        parent::_construct();
        $this->_init(ListingProductResource::class);
    }

    public function init(int $listingId, int $magentoProductId, int $categoryId): self
    {
        $this
            ->setListingId($listingId)
            ->setMagentoProductId($magentoProductId)
            ->setStatusNotListed(self::STATUS_CHANGER_USER)
            ->setTemplateCategoryId($categoryId);

        return $this;
    }

    public function fillFromUnmanagedProduct(\M2E\Otto\Model\Listing\Other $unmanagedProduct): self
    {
        $this->setOttoProductSku($unmanagedProduct->getSku())
             ->setStatus($unmanagedProduct->getStatus(), self::STATUS_CHANGER_COMPONENT)
             ->setOnlineTitle($unmanagedProduct->getTitle())
             ->setOnlineQty($unmanagedProduct->getQty())
             ->setOnlinePrice($unmanagedProduct->getPrice())
             ->setOttoProductUrl($unmanagedProduct->getOttoProductUrl())
             ->setOttoProductMoin($unmanagedProduct->getOttoProductMoin())
             ->setOnlineProductReference($unmanagedProduct->getProductReference())
             ->setOnlineShippingProfileId($unmanagedProduct->getShippingProfileId());

        if ($unmanagedProduct->getDeliveryType() !== null) {
            $this->setOnlineDeliveryType($unmanagedProduct->getDeliveryType());
        }

        if ($unmanagedProduct->getCategory() !== null) {
            $this->setOnlineCategoryName($unmanagedProduct->getCategory());
        }

        if ($unmanagedProduct->isProductIncomplete()) {
            $this->makeProductIncomplete();
        }

        $additionalData = $this->getAdditionalData();
        $additionalData[self::MOVING_LISTING_OTHER_SOURCE_KEY] = $unmanagedProduct->getId();

        $this->setAdditionalData($additionalData);

        return $this;
    }

    public function initListing(\M2E\Otto\Model\Listing $listing): void
    {
        $this->listing = $listing;
    }

    public function getListing(): \M2E\Otto\Model\Listing
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->listing)) {
            return $this->listing;
        }

        return $this->listing = $this->listingRepository->get($this->getListingId());
    }

    public function getAccount(): Account
    {
        return $this->getListing()->getAccount();
    }

    public function getMagentoProduct(): \M2E\Otto\Model\Magento\Product\Cache
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->magentoProductModel)) {
            return $this->magentoProductModel;
        }

        return $this->magentoProductModel = $this->magentoProductFactory->create()
            ->setProductId($this->getMagentoProductId())
            ->setStoreId($this->getListing()->getStoreId())
            ->setStatisticId($this->getId());
    }

    public function getDataProvider(): Product\DataProvider
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->dataProvider)) {
            return $this->dataProvider;
        }

        return $this->dataProvider = $this->dataProviderFactory->create($this);
    }

    public function getListingId(): int
    {
        return (int)$this->getData('listing_id');
    }

    public function getMagentoProductId(): int
    {
        return (int)$this->getData(ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID);
    }

    public function isStatusNotListed(): bool
    {
        return $this->getStatus() === self::STATUS_NOT_LISTED;
    }

    public function isStatusListed(): bool
    {
        return $this->getStatus() === self::STATUS_LISTED;
    }

    public function isStatusInactive(): bool
    {
        return $this->getStatus() === self::STATUS_INACTIVE;
    }

    public function setStatusListed(string $ottoProductSku, int $changer): self
    {
        $this
            ->setStatus(self::STATUS_LISTED, $changer)
            ->setOttoProductSku($ottoProductSku);

        return $this;
    }

    public function setStatusNotListed(int $changer): self
    {
        $this->setStatus(self::STATUS_NOT_LISTED, $changer)

            ->setData(ListingProductResource::COLUMN_OTTO_PRODUCT_SKU, null)
            ->setData(ListingProductResource::COLUMN_OTTO_PRODUCT_URL, null)
            ->setData(ListingProductResource::COLUMN_PRODUCT_MOIN, null)

            ->setData(ListingProductResource::COLUMN_ONLINE_EAN, null)
            ->setData(ListingProductResource::COLUMN_ONLINE_SKU, null)
            ->setData(ListingProductResource::COLUMN_ONLINE_PRODUCT_REFERENCE, null)

            ->setData(ListingProductResource::COLUMN_ONLINE_TITLE, null)
            ->setData(ListingProductResource::COLUMN_ONLINE_DESCRIPTION, null)
            ->setData(ListingProductResource::COLUMN_ONLINE_BRAND_NAME, null)
            ->setData(ListingProductResource::COLUMN_ONLINE_BRAND_ID, null)
            ->setData(ListingProductResource::COLUMN_ONLINE_IMAGES_DATA, null)

            ->setData(ListingProductResource::COLUMN_ONLINE_MPN, null)
            ->setData(ListingProductResource::COLUMN_ONLINE_MANUFACTURER, null)

            ->setData(ListingProductResource::COLUMN_ONLINE_CATEGORY, null)
            ->setData(ListingProductResource::COLUMN_ONLINE_CATEGORY_ATTRIBUTES_DATA, null)

            ->setData(ListingProductResource::COLUMN_ONLINE_PRICE, null)
            ->setData(ListingProductResource::COLUMN_ONLINE_QTY, null)

            ->setData(ListingProductResource::COLUMN_ONLINE_DELIVERY_DATA, null)
            ->setData(ListingProductResource::COLUMN_ONLINE_DELIVERY_TYPE, null)
            ->setData(ListingProductResource::COLUMN_ONLINE_SHIPPING_PROFILE_ID, null);

        if ($this->isProductIncomplete()) {
            $this->makeProductComplete();
        }

        return $this;
    }

    public function setStatusInactive(int $changer): self
    {
        $this->setStatus(self::STATUS_INACTIVE, $changer);

        return $this;
    }

    public function setStatus(int $status, int $changer): self
    {
        $this->setData(ListingProductResource::COLUMN_STATUS, $status)
             ->setStatusChanger($changer);

        $this->setStatusChangeDate(\M2E\Core\Helper\Date::createCurrentGmt());

        return $this;
    }

    public function getStatusChangeDate(): ?\DateTimeImmutable
    {
        $value = $this->getData(ListingProductResource::COLUMN_STATUS_CHANGE_DATE);
        if (empty($value)) {
            return null;
        }

        return \DateTimeImmutable::createFromMutable(\M2E\Core\Helper\Date::createDateGmt($value));
    }

    private function setStatusChangeDate(\DateTime $date): self
    {
        $this->setData(ListingProductResource::COLUMN_STATUS_CHANGE_DATE, $date->format('Y-m-d H:i:s'));

        return $this;
    }

    public function getStatus(): int
    {
        return (int)$this->getData('status');
    }

    public function isProductIncomplete(): bool
    {
        return (bool)$this->getData(ListingProductResource::COLUMN_IS_INCOMPLETE);
    }

    public function makeProductIncomplete(): void
    {
        $this->setData(ListingProductResource::COLUMN_IS_INCOMPLETE, 1);
    }

    public function makeProductComplete(): void
    {
        $this->setData(ListingProductResource::COLUMN_IS_INCOMPLETE, 0);
    }

    public function isStatusChangerUser(): bool
    {
        return $this->getStatusChanger() === self::STATUS_CHANGER_USER;
    }

    public function getStatusChanger(): int
    {
        return (int)$this->getData('status_changer');
    }

    public function setAdditionalData(array $value): self
    {
        $this->setData(ListingProductResource::COLUMN_ADDITIONAL_DATA, json_encode($value));

        return $this;
    }

    public function getAdditionalData(): array
    {
        $value = $this->getData(ListingProductResource::COLUMN_ADDITIONAL_DATA);
        if (empty($value)) {
            return [];
        }

        return (array)json_decode($value, true);
    }

    public function isListable(): bool
    {
        return $this->isStatusNotListed() && !$this->isProductIncomplete();
    }

    public function isRelistable(): bool
    {
        return $this->isStatusInactive() && !$this->isProductIncomplete();
    }

    public function isRevisable(): bool
    {
        return $this->isStatusListed() && !$this->isProductIncomplete();
    }

    public function isStoppable(): bool
    {
        return $this->isStatusListed() && !$this->isProductIncomplete();
    }

    public function isRetirable(): bool
    {
        return $this->isStatusListed() || $this->isStatusInactive();
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getSellingFormatTemplate(): \M2E\Otto\Model\Template\SellingFormat
    {
        return $this->getListing()->getTemplateSellingFormat();
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getSynchronizationTemplate(): \M2E\Otto\Model\Template\Synchronization
    {
        return $this->getListing()->getTemplateSynchronization();
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getDescriptionTemplate(): ?\M2E\Otto\Model\Template\Description
    {
        return $this->getListing()->getTemplateDescription();
    }

    /**
     * @return \M2E\Otto\Model\Policy\ShippingDataProvider
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getShippingPolicyDataProvider(): Policy\ShippingDataProvider
    {
        return $this->shippingDataProviderFactory->createShipping($this->getShippingTemplate(), $this);
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getShippingTemplate(): \M2E\Otto\Model\Template\Shipping
    {
        return $this->getListing()->getTemplateShipping();
    }

    public function getRenderedDescription(): string
    {
        return $this->descriptionRendererFactory->create($this)
                                                ->parseTemplate(
                                                    $this->getDescriptionTemplateSource()->getDescription(),
                                                );
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getDescriptionTemplateSource(): \M2E\Otto\Model\Template\Description\Source
    {
        return $this->getDescriptionTemplate()->getSource($this->getMagentoProduct());
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getSellingFormatTemplateSource(): \M2E\Otto\Model\Template\SellingFormat\Source
    {
        return $this->getSellingFormatTemplate()->getSource($this->getMagentoProduct());
    }

    public function hasCategoryTemplate(): bool
    {
        return $this->getTemplateCategoryId() !== 0;
    }

    public function getTemplateCategoryId(): int
    {
        return (int)$this->getData(ListingProductResource::COLUMN_TEMPLATE_CATEGORY_ID);
    }

    public function getCategoryTemplate(): Category
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->category)) {
            return $this->category;
        }

        return $this->category = $this->categoryRepository->get($this->getTemplateCategoryId());
    }

    public function setTemplateCategoryId(int $id): self
    {
        $this->setData(ListingProductResource::COLUMN_TEMPLATE_CATEGORY_ID, $id);

        return $this;
    }

    public function getOnlineTitle(): string
    {
        return (string)$this->getData(ListingProductResource::COLUMN_ONLINE_TITLE);
    }

    public function setOnlineDescription(string $value): self
    {
        $this->setData(ListingProductResource::COLUMN_ONLINE_DESCRIPTION, $value);

        return $this;
    }

    public function getOnlineDescription(): string
    {
        return (string)$this->getData(ListingProductResource::COLUMN_ONLINE_DESCRIPTION);
    }

    // ---------------------------------------

    public function getOnlineCurrentPrice(): float
    {
        return (float)$this->getData(\M2E\Otto\Model\ResourceModel\Product::COLUMN_ONLINE_PRICE);
    }

    public function getOnlineSalePrice(): ?float
    {
        $salePrice = $this->getData(\M2E\Otto\Model\ResourceModel\Product::COLUMN_ONLINE_SALE_PRICE);
        if ($salePrice === null) {
            return null;
        }

        return (float)$salePrice;
    }

    public function setOnlineSalePrice(?float $salePrice): self
    {
        $this->setData(\M2E\Otto\Model\ResourceModel\Product::COLUMN_ONLINE_SALE_PRICE, $salePrice);

        return $this;
    }

    public function getOnlineSalePriceStartDate(): ?string
    {
        return $this->getData(\M2E\Otto\Model\ResourceModel\Product::COLUMN_ONLINE_SALE_PRICE_START_DATE);
    }

    public function setOnlineSalePriceStartDate(?string $salePriceStartDate): self
    {
        $this->setData(\M2E\Otto\Model\ResourceModel\Product::COLUMN_ONLINE_SALE_PRICE_START_DATE, $salePriceStartDate);

        return $this;
    }

    public function getOnlineSalePriceEndDate(): ?string
    {
        return $this->getData(\M2E\Otto\Model\ResourceModel\Product::COLUMN_ONLINE_SALE_PRICE_END_DATE);
    }

    public function setOnlineSalePriceEndDate(?string $salePriceEndDate): self
    {
        $this->setData(\M2E\Otto\Model\ResourceModel\Product::COLUMN_ONLINE_SALE_PRICE_END_DATE, $salePriceEndDate);

        return $this;
    }

    public function getOnlineMsrp(): ?float
    {
        $msrp = $this->getData(\M2E\Otto\Model\ResourceModel\Product::COLUMN_ONLINE_MSRP);
        if ($msrp === null) {
            return null;
        }

        return (float)$msrp;
    }

    public function setOnlineMsrp(?float $msrp): self
    {
        $this->setData(\M2E\Otto\Model\ResourceModel\Product::COLUMN_ONLINE_MSRP, $msrp);

        return $this;
    }

    public function getOnlineQty(): int
    {
        return (int)$this->getData(ListingProductResource::COLUMN_ONLINE_QTY);
    }

    // ----------------------------------------

    public function getOnlineCategoryName(): string
    {
        return (string)$this->getData(ListingProductResource::COLUMN_ONLINE_CATEGORY);
    }

    public function setOnlineCategoryAttributesData(string $value): self
    {
        $this->setData(ListingProductResource::COLUMN_ONLINE_CATEGORY_ATTRIBUTES_DATA, $value);

        return $this;
    }

    public function getOnlineCategoryAttributesData(): string
    {
        return (string)$this->getData(ListingProductResource::COLUMN_ONLINE_CATEGORY_ATTRIBUTES_DATA);
    }

    public function setOnlineImagesData(string $value): self
    {
        $this->setData(ListingProductResource::COLUMN_ONLINE_IMAGES_DATA, $value);

        return $this;
    }

    public function getOnlineImagesData(): string
    {
        return (string)$this->getData(ListingProductResource::COLUMN_ONLINE_IMAGES_DATA);
    }

    public function setOnlineBrandName(?string $name): self
    {
        $this->setData(ListingProductResource::COLUMN_ONLINE_BRAND_NAME, $name);

        return $this;
    }

    public function getOnlineBrandName(): string
    {
        return (string)$this->getData(ListingProductResource::COLUMN_ONLINE_BRAND_NAME);
    }

    public function setOnlineBrandId(?string $id): self
    {
        $this->setData(ListingProductResource::COLUMN_ONLINE_BRAND_ID, $id);

        return $this;
    }

    public function getOnlineBrandId(): string
    {
        return (string)$this->getData(ListingProductResource::COLUMN_ONLINE_BRAND_ID);
    }

    public function setOnlinePrice(float $value): self
    {
        //check && set price last_modified_date (price_actualize_date)
        $this->setData(ListingProductResource::COLUMN_ONLINE_PRICE, $value);

        return $this;
    }

    public function setOnlineQty(int $value): self
    {
        //check && set qty last_modified_date (qty_actualize_date)
        $this->setData(ListingProductResource::COLUMN_ONLINE_QTY, $value);

        return $this;
    }

    public function setOnlineProductReference(string $value): self
    {
        $this->setData(ListingProductResource::COLUMN_ONLINE_PRODUCT_REFERENCE, $value);

        return $this;
    }

    public function setOnlineVat(string $value): self
    {
        $this->setData(ListingProductResource::COLUMN_ONLINE_VAT, $value);

        return $this;
    }

    public function setOnlineEan(string $value): self
    {
        $this->setData(ListingProductResource::COLUMN_ONLINE_EAN, $value);

        return $this;
    }

    /** @deprecated */
    public function setOnlineDeliveryData(string $deliveryType, int $deliveryTime): self
    {
        $value = [
            'delivery_type' => $deliveryType,
            'delivery_time' => $deliveryTime,
        ];

        $this->setData(ListingProductResource::COLUMN_ONLINE_DELIVERY_DATA, json_encode($value));

        return $this;
    }

    public function setOnlineShippingProfileId(?string $shippingProfileId): self
    {
        $this->setData(ListingProductResource::COLUMN_ONLINE_SHIPPING_PROFILE_ID, $shippingProfileId);

        return $this;
    }

    public function getOnlineShippingProfileId(): ?string
    {
        return $this->getData(ListingProductResource::COLUMN_ONLINE_SHIPPING_PROFILE_ID);
    }

    public function setOnlineDeliveryType(string $deliveryType): self
    {
        $this->setData(ListingProductResource::COLUMN_ONLINE_DELIVERY_TYPE, $deliveryType);

        return $this;
    }

    public function getOnlineDeliveryType(): ?string
    {
        return $this->getData(ListingProductResource::COLUMN_ONLINE_DELIVERY_TYPE);
    }

    // ---------------------------------------

    public function changeListing(\M2E\Otto\Model\Listing $listing): self
    {
        $this->setListingId($listing->getId());
        $this->initListing($listing);

        return $this;
    }

    private function setListingId(int $listingId): self
    {
        $this->setData(ListingProductResource::COLUMN_LISTING_ID, $listingId);

        return $this;
    }

    private function setMagentoProductId(int $magentoProductId): self
    {
        $this->setData(ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID, $magentoProductId);

        return $this;
    }

    public function setOttoProductSku(string $productId): self
    {
        $this->setData(ListingProductResource::COLUMN_OTTO_PRODUCT_SKU, $productId);

        return $this;
    }

    public function getOttoProductSku(): string
    {
        return (string)$this->getData(ListingProductResource::COLUMN_OTTO_PRODUCT_SKU);
    }

    public function setOnlineSku(string $onlineSku): self
    {
        $this->setData(ListingProductResource::COLUMN_ONLINE_SKU, $onlineSku);

        return $this;
    }

    public function getOnlineSku(): string
    {
        return (string)$this->getData(ListingProductResource::COLUMN_ONLINE_SKU);
    }

    public function getOnlineProductReference(): string
    {
        return (string)$this->getData(ListingProductResource::COLUMN_ONLINE_PRODUCT_REFERENCE);
    }

    public function getOnlineEan(): string
    {
        return (string)$this->getData(ListingProductResource::COLUMN_ONLINE_EAN);
    }

    public function getOnlineVat(): string
    {
        return (string)$this->getData(ListingProductResource::COLUMN_ONLINE_VAT);
    }

    /** @deprecated */
    public function getOnlineDeliveryData(): array
    {
        $value = $this->getData(ListingProductResource::COLUMN_ONLINE_DELIVERY_DATA);
        if (empty($value)) {
            return [];
        }

        return (array)json_decode($value, true);
    }

    public function setOnlineTitle(string $onlineTitle): self
    {
        $this->setData(ListingProductResource::COLUMN_ONLINE_TITLE, $onlineTitle);

        return $this;
    }

    public function setOnlineCategoryName(string $mainCategory): self
    {
        $this->setData(ListingProductResource::COLUMN_ONLINE_CATEGORY, $mainCategory);

        return $this;
    }

    public function setOnlineMpn(?string $mpn): self
    {
        $this->setData(ListingProductResource::COLUMN_ONLINE_MPN, $mpn);

        return $this;
    }

    public function getOnlineMpn(): ?string
    {
        return $this->getData(ListingProductResource::COLUMN_ONLINE_MPN);
    }

    public function getOnlineManufacturer(): ?string
    {
        return $this->getData(ListingProductResource::COLUMN_ONLINE_MANUFACTURER);
    }

    public function setOnlineManufacturer(?string $manufacturer): self
    {
        $this->setData(ListingProductResource::COLUMN_ONLINE_MANUFACTURER, $manufacturer);

        return $this;
    }

    public function getMarketplaceErrors(): ?MessageCollection
    {
        $marketplaceErrorsJson = $this->getData(ListingProductResource::COLUMN_MARKETPLACE_ERRORS);
        if (empty($marketplaceErrorsJson)) {
            return null;
        }
        $messageErrors = json_decode($marketplaceErrorsJson, true);

        return self::getMessageCollection($messageErrors);
    }

    public function setMarketplaceErrors(?MessageCollection $marketplaceErrors): self
    {
        if ($marketplaceErrors === null) {
            $this->resetMarketplaceErrors();

            return $this;
        }

        $messages = [];
        foreach ($marketplaceErrors->getMessages() as $message) {
            $messages[] = [
                'code' => $message->getCode(),
                'text' => $message->getText(),
                'type' => $message->getType(),
                'sender' => \M2E\Core\Model\Connector\Response\Message::SENDER_COMPONENT, //todo method in Core
            ];
        }

        $this->setData(ListingProductResource::COLUMN_MARKETPLACE_ERRORS, json_encode($messages));

        return $this;
    }

    public function resetMarketplaceErrors(): void
    {
        $this->setData(ListingProductResource::COLUMN_MARKETPLACE_ERRORS, null);
    }

    public function hasOttoProductUrl(): bool
    {
        return $this->getOttoProductUrl() !== null;
    }

    public function setOttoProductUrl(?string $url): self
    {
        $this->setData(ListingProductResource::COLUMN_OTTO_PRODUCT_URL, $url);

        return $this;
    }

    public function getOttoProductUrl(): ?string
    {
        return $this->getData(ListingProductResource::COLUMN_OTTO_PRODUCT_URL);
    }

    public function setOttoProductMoin(?string $moin): self
    {
        $this->setData(ListingProductResource::COLUMN_PRODUCT_MOIN, $moin);

        return $this;
    }

    public function getOttoProductMoin(): ?string
    {
        return $this->getData(ListingProductResource::COLUMN_PRODUCT_MOIN);
    }

    private function setStatusChanger(int $statusChanger): void
    {
        $this->validateStatusChanger($statusChanger);
        $this->setData(ListingProductResource::COLUMN_STATUS_CHANGER, $statusChanger);
    }

    // ----------------------------------------

    public function getCurrencyCode(): string
    {
        return \M2E\Otto\Model\Currency::CURRENCY_EUR;
    }

    public function hasBlockingByError(): bool
    {
        $rawDate = $this->getData(ListingProductResource::COLUMN_LAST_BLOCKING_ERROR_DATE);
        if (empty($rawDate)) {
            return false;
        }

        $lastBlockingDate = \M2E\Core\Helper\Date::createDateGmt($rawDate);
        $twentyFourHoursAgoDate = \M2E\Core\Helper\Date::createCurrentGmt()->modify('-24 hour');

        return $lastBlockingDate->getTimestamp() > $twentyFourHoursAgoDate->getTimestamp();
    }

    public function removeBlockingByError(): self
    {
        $this->setData(ListingProductResource::COLUMN_LAST_BLOCKING_ERROR_DATE, null);

        return $this;
    }

    // ----------------------------------------

    public static function getStatusTitle(int $status): string
    {
        $statuses = [
            self::STATUS_NOT_LISTED => (string)__('Not Listed'),
            self::STATUS_LISTED => (string)__('Active'),
            self::STATUS_INACTIVE => (string)__('Inactive'),
        ];

        return $statuses[$status] ?? 'Unknown';
    }

    public static function getIncompleteStatusTitle(): string
    {
        return (string)__('Incomplete');
    }

    private function validateStatusChanger(int $changer): void
    {
        $allowed = [
            self::STATUS_CHANGER_SYNCH,
            self::STATUS_CHANGER_USER,
            self::STATUS_CHANGER_COMPONENT,
            self::STATUS_CHANGER_OBSERVER,
        ];

        if (!in_array($changer, $allowed)) {
            throw new \M2E\Otto\Model\Exception\Logic(sprintf('Status changer %s not valid.', $changer));
        }
    }

    public static function getMessageCollection(array $messageErrors): MessageCollection
    {
        $messages = [];
        foreach ($messageErrors as $messageError) {
            $message = new \M2E\Core\Model\Connector\Response\Message();
            $message->initFromPreparedData(
                $messageError['text'],
                $messageError['type'],
                $messageError['sender'],
                $messageError['code']
            );
            $messages[] = $message;
        }

        return new MessageCollection($messages);
    }
}
