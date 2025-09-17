<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Type\ListAction;

use M2E\Otto\Model\Product\DataProvider\BrandProvider;
use M2E\Otto\Model\Product\DataProvider\DeliveryProvider;
use M2E\Otto\Model\Product\DataProvider\DescriptionProvider;
use M2E\Otto\Model\Product\DataProvider\PriceProvider;
use M2E\Otto\Model\Product\DataProvider\QtyProvider;
use M2E\Otto\Model\Product\DataProvider\TitleProvider;
use M2E\Otto\Model\Product\DataProvider\VatProvider;

class Response extends \M2E\Otto\Model\Otto\Listing\Product\Action\Type\AbstractResponse
{
    private \M2E\Otto\Model\Product\Repository $productRepository;
    protected \Magento\Framework\Locale\CurrencyInterface $localeCurrency;

    public function __construct(
        \M2E\Otto\Model\Product\Repository $productRepository,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \M2E\Otto\Model\Tag\ListingProduct\Buffer $tagBuffer,
        \M2E\Otto\Model\Otto\TagFactory $tagFactory
    ) {
        parent::__construct($tagBuffer, $tagFactory);

        $this->productRepository = $productRepository;
        $this->localeCurrency = $localeCurrency;
    }

    public function process(): void
    {
        if (!$this->isSuccess()) {
            $this->processFail();

            if ($this->isProductCreated()) {
                $this->processIncompleteProduct();
            }

            return;
        }

        $this->processSuccess();
    }

    public function processFail(): void
    {
        $responseData = $this->getResponseData();
        foreach ($responseData['products'][0]['messages'] as $message) {
            $this->getLogBuffer()->addFail($message['title']);
        }

        $this->addTags($responseData['products'][0]['messages']);
    }

    private function processIncompleteProduct(): void
    {
        $product = $this->getProduct();
        $product->makeProductIncomplete();

        $responseData = $this->getResponseData();
        if (array_key_exists('product_url', $responseData['products'][0])) {
            $product->setOttoProductUrl($responseData['products'][0]['product_url']);
        }

        if (array_key_exists('moin', $responseData['products'][0])) {
            $product->setOttoProductMoin($responseData['products'][0]['moin']);
        }
        $this->productRepository->save($product);

        $this->getLogBuffer()->addWarning(
            (string)__('Product has been created but is not yet available. Item Status was changed to incomplete.')
        );
    }

    protected function processSuccess(): void
    {
        $requestMetadata = $this->getRequestMetaData();
        $responseData = $this->getResponseData();

        $product = $this->getProduct();

        if (isset($requestMetadata[BrandProvider::NICK]['online_brand_name']) || isset($requestMetadata['brand_name'])) {
            $product->setOnlineBrandName($requestMetadata[BrandProvider::NICK]['online_brand_name'] ?? $requestMetadata['brand_name']);
        }

        if (isset($requestMetadata[BrandProvider::NICK]['online_brand_id']) || isset($requestMetadata['brand_id'])) {
            $product->setOnlineBrandId($requestMetadata[BrandProvider::NICK]['online_brand_id'] ?? $requestMetadata['brand_id']);
        }

        if (array_key_exists('category_attributes_hash', $requestMetadata)) {
            $product->setOnlineCategoryAttributesData($requestMetadata['category_attributes_hash']);
        }

        if (array_key_exists('images_hash', $requestMetadata)) {
            $product->setOnlineImagesData($requestMetadata['images_hash']);
        }

        if (array_key_exists('mpn', $requestMetadata)) {
            $product->setOnlineMpn($requestMetadata['mpn']);
        }

        if (array_key_exists('manufacturer', $requestMetadata)) {
            $product->setOnlineManufacturer($requestMetadata['manufacturer']);
        }

        if (array_key_exists('product_url', $responseData['products'][0])) {
            $product->setOttoProductUrl($responseData['products'][0]['product_url']);
        }

        if (array_key_exists('moin', $responseData['products'][0])) {
            $product->setOttoProductMoin($responseData['products'][0]['moin']);
        }

        if (array_key_exists('sale_price', $requestMetadata)) {
            $salesPriceMetadata = $requestMetadata['sale_price'];
            $product->setOnlineSalePrice($salesPriceMetadata['amount'] ?? null);
            $product->setOnlineSalePriceStartDate($salesPriceMetadata['start_date'] ?? null);
            $product->setOnlineSalePriceEndDate($salesPriceMetadata['end_date'] ?? null);
        }

        if (array_key_exists('msrp_price', $requestMetadata)) {
            $product->setOnlineMsrp($requestMetadata['msrp_price']['amount'] ?? null);
        }

        $product
            ->setOnlineQty($requestMetadata[QtyProvider::NICK]['qty'] ?? $requestMetadata['qty'])
            ->setOnlinePrice($requestMetadata[PriceProvider::NICK]['price'] ?? $requestMetadata['price'])
            ->setOnlineTitle($requestMetadata[TitleProvider::NICK]['online_title'] ?? $requestMetadata['title'])
            ->setOnlineDescription($requestMetadata[DescriptionProvider::NICK]['online_description'] ?? $requestMetadata['description_hash'])
            ->setOnlineCategoryName($requestMetadata['Categories']['online_category'] ?? $requestMetadata['category_name'])
            ->setOnlineVat($requestMetadata[VatProvider::NICK]['online_vat'] ?? $requestMetadata['vat'])
            ->setOnlineEan($requestMetadata['Identifier']['online_ean'] ?? $requestMetadata['ean'])
            ->setOttoProductSku($responseData['products'][0]['sku'])
            ->setOnlineProductReference($requestMetadata['Identifier']['online_product_reference'] ?? $requestMetadata['product_reference'])
            ->setOnlineDeliveryData(
                $requestMetadata[DeliveryProvider::NICK]['online_delivery_type'] ?? $requestMetadata['delivery_type'],
                $requestMetadata[DeliveryProvider::NICK]['online_delivery_time'] ?? $requestMetadata['delivery_time']
            )
            ->setOnlineDeliveryType($requestMetadata['delivery_type'])
            ->setOnlineShippingProfileId($requestMetadata['shipping_profile_id'] ?? null)
            ->setStatus(\M2E\Otto\Model\Product::STATUS_LISTED, $this->getStatusChanger())
            ->removeBlockingByError();

        $product->makeProductComplete();

        $this->productRepository->save($product);
    }

    private function isSuccess(): bool
    {
        $responseData = $this->getResponseData();

        return !empty($responseData['products'][0]['status']);
    }

    private function isProductCreated(): bool
    {
        $responseData = $this->getResponseData();

        return $responseData['products'][0]['is_product_created'];
    }

    public function generateResultMessage(): void
    {
        if (!$this->isSuccess()) {
            $this->getLogBuffer()->addFail('Product failed to be listed.');

            return;
        }

        $currencyCode = $this->getProduct()->getCurrencyCode();
        $currency = $this->localeCurrency->getCurrency($currencyCode);

        $onlineQty = $this->getProduct()->getOnlineQty();
        $onlinePrice = $this->getProduct()->getOnlineCurrentPrice();

        $message = sprintf(
            'Product was Listed with QTY %d, Price %s',
            $onlineQty,
            $currency->toCurrency($onlinePrice),
        );

        $this->getLogBuffer()->addSuccess($message);
    }
}
