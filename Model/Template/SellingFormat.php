<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template;

use M2E\Otto\Model\ResourceModel\Template\SellingFormat as SellingFormatResource;

class SellingFormat extends \M2E\Otto\Model\ActiveRecord\AbstractModel implements PolicyInterface
{
    public const QTY_MODE_PRODUCT = 1;
    public const QTY_MODE_NUMBER = 3;
    public const QTY_MODE_ATTRIBUTE = 4;
    public const QTY_MODE_PRODUCT_FIXED = 5;

    public const PRICE_MODE_NONE = 0;
    public const PRICE_MODE_PRODUCT = 1;
    public const PRICE_MODE_SPECIAL = 2;
    public const PRICE_MODE_ATTRIBUTE = 3;
    public const PRICE_MODE_TIER = 4;

    public const SALE_PRICE_MODE_NONE = 0;
    public const SALE_PRICE_MODE_ATTRIBUTE = 1;

    public const MSRP_MODE_NONE = 0;
    public const MSRP_MODE_ATTRIBUTE = 1;

    public const PRICE_MODIFIER_NONE = 0;
    public const PRICE_MODIFIER_ABSOLUTE_INCREASE = 1;
    public const PRICE_MODIFIER_ABSOLUTE_DECREASE = 2;
    public const PRICE_MODIFIER_PERCENTAGE_INCREASE = 3;
    public const PRICE_MODIFIER_PERCENTAGE_DECREASE = 4;
    public const PRICE_MODIFIER_ATTRIBUTE = 5;

    public const PRICE_COEFFICIENT_ABSOLUTE_INCREASE = 1;
    public const PRICE_COEFFICIENT_PERCENTAGE_INCREASE = 3;
    public const PRICE_COEFFICIENT_PERCENTAGE_DECREASE = 4;
    public const PRICE_COEFFICIENT_ATTRIBUTE = 5;

    public const QTY_MODIFICATION_MODE_ON = 1;
    public const QTY_MODIFICATION_MODE_OFF = 0;

    public const PRICE_COEFFICIENT_ABSOLUTE_DECREASE = 2;
    public const PRICE_COEFFICIENT_NONE = 0;

    public const QTY_MIN_POSTED_DEFAULT_VALUE = 1;
    public const QTY_MAX_POSTED_DEFAULT_VALUE = 100;

    public const PRICE_DISCOUNT_MAP_EXPOSURE_NONE = 0;
    public const PRICE_DISCOUNT_MAP_EXPOSURE_DURING_CHECKOUT = 1;
    public const PRICE_DISCOUNT_MAP_EXPOSURE_PRE_CHECKOUT = 2;

    /** @var \M2E\Otto\Model\Template\SellingFormat\Source[] */
    private array $sellingSourceModels = [];
    private SellingFormat\SourceFactory $sourceFactory;

    public function __construct(
        \M2E\Otto\Model\Template\SellingFormat\SourceFactory $sourceFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct(
            $context,
            $registry
        );
        $this->sourceFactory = $sourceFactory;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(SellingFormatResource::class);
    }

    public function getNick(): string
    {
        return \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_SELLING_FORMAT;
    }

    // ----------------------------------------

    public function getSource(
        \M2E\Otto\Model\Magento\Product $magentoProduct
    ): SellingFormat\Source {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->sellingSourceModels[$productId])) {
            return $this->sellingSourceModels[$productId];
        }

        $this->sellingSourceModels[$productId] = $this->sourceFactory->create();
        $this->sellingSourceModels[$productId]->setMagentoProduct($magentoProduct);
        $this->sellingSourceModels[$productId]->setSellingFormatTemplate($this);

        return $this->sellingSourceModels[$productId];
    }

    // ----------------------------------------

    public function getTitle(): string
    {
        return (string)$this->getData(SellingFormatResource::COLUMN_TITLE);
    }

    public function getCreateDate()
    {
        return $this->getData(SellingFormatResource::COLUMN_CREATE_DATE);
    }

    public function getUpdateDate()
    {
        return $this->getData(SellingFormatResource::COLUMN_UPDATE_DATE);
    }

    // ----------------------------------------

    public function getQtyMode(): int
    {
        return (int)$this->getData(SellingFormatResource::COLUMN_QTY_MODE);
    }

    public function getQtyNumber(): int
    {
        return (int)$this->getData(SellingFormatResource::COLUMN_QTY_CUSTOM_VALUE);
    }

    public function getQtySource(): array
    {
        return [
            'mode' => $this->getQtyMode(),
            'value' => $this->getQtyNumber(),
            'attribute' => $this->getData(SellingFormatResource::COLUMN_QTY_CUSTOM_ATTRIBUTE),
            'qty_modification_mode' => $this->getQtyModificationMode(),
            'qty_min_posted_value' => $this->getQtyMinPostedValue(),
            'qty_max_posted_value' => $this->getQtyMaxPostedValue(),
            'qty_percentage' => $this->getQtyPercentage(),
        ];
    }

    public function getQtyPercentage(): int
    {
        return (int)$this->getData(SellingFormatResource::COLUMN_QTY_PERCENTAGE);
    }

    public function getQtyModificationMode(): int
    {
        return (int)$this->getData(SellingFormatResource::COLUMN_QTY_MODIFICATION_MODE);
    }

    public function getQtyMinPostedValue(): int
    {
        return (int)$this->getData(SellingFormatResource::COLUMN_QTY_MIN_POSTED_VALUE);
    }

    public function getQtyMaxPostedValue(): int
    {
        return (int)$this->getData(SellingFormatResource::COLUMN_QTY_MAX_POSTED_VALUE);
    }

    public function getFixedPriceMode(): int
    {
        return (int)$this->getData(SellingFormatResource::COLUMN_FIXED_PRICE_MODE);
    }

    public function getFixedPriceModifier(): array
    {
        $modifier = $this->getData(SellingFormatResource::COLUMN_FIXED_PRICE_MODIFIER);
        if (empty($modifier)) {
            return [];
        }

        return json_decode($modifier, true);
    }

    public function getSalePriceMode(): int
    {
        return (int)$this->getData(SellingFormatResource::COLUMN_SALE_PRICE_MODE);
    }

    public function getSalePriceAttribute(): string
    {
        return (string)$this->getData(SellingFormatResource::COLUMN_SALE_PRICE_ATTRIBUTE);
    }

    public function getSalePriceStartDateMode(): int
    {
        return (int)$this->getData(SellingFormatResource::COLUMN_SALE_PRICE_START_DATE_MODE);
    }

    public function getSalePriceStartDateAttribute(): string
    {
        return (string)$this->getData(SellingFormatResource::COLUMN_SALE_PRICE_START_DATE_VALUE);
    }

    public function getSalePriceEndDateMode(): int
    {
        return (int)$this->getData(SellingFormatResource::COLUMN_SALE_PRICE_END_DATE_MODE);
    }

    public function getSalePriceEndDateAttribute(): string
    {
        return (string)$this->getData(SellingFormatResource::COLUMN_SALE_PRICE_END_DATE_VALUE);
    }

    public function getMsrpMode(): int
    {
        return (int)$this->getData(SellingFormatResource::COLUMN_MSRP_MODE);
    }

    public function getMsrpAttribute(): string
    {
        return (string)$this->getData(SellingFormatResource::COLUMN_MSRP_ATTRIBUTE);
    }

    public function getFixedPriceSource(): array
    {
        return [
            'mode' => $this->getFixedPriceMode(),
            'modifier' => $this->getFixedPriceModifier(),
            'attribute' => $this->getData(SellingFormatResource::COLUMN_FIXED_PRICE_CUSTOM_ATTRIBUTE),
        ];
    }
}
