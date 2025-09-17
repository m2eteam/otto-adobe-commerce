<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Type\Revise;

use M2E\Otto\Model\Product\DataProvider\PriceProvider;
use M2E\Otto\Model\Product\DataProvider\QtyProvider;

class Response extends \M2E\Otto\Model\Otto\Listing\Product\Action\Type\AbstractResponse
{
    private \M2E\Otto\Model\Product\Repository $productRepository;
    protected \Magento\Framework\Locale\CurrencyInterface $localeCurrency;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Type\Revise\LoggerFactory $loggerFactory;

    public function __construct(
        \M2E\Otto\Model\Product\Repository $productRepository,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \M2E\Otto\Model\Tag\ListingProduct\Buffer $tagBuffer,
        \M2E\Otto\Model\Otto\TagFactory $tagFactory,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Type\Revise\LoggerFactory $loggerFactory
    ) {
        parent::__construct($tagBuffer, $tagFactory);

        $this->localeCurrency = $localeCurrency;
        $this->loggerFactory = $loggerFactory;
        $this->productRepository = $productRepository;
    }

    public function process(): void
    {
        $responseData = $this->getResponseData();
        if (!empty($responseData['products'][0]['messages'])) {
            $this->addTags($responseData['products'][0]['messages']);
        }

        if (!$this->validateProduct()) {
            return;
        }

        $this->processSuccess();
    }

    private function validateProduct(): bool
    {
        return $this->getResponseData()['products'][0]['sku'] === $this->getProduct()->getOttoProductSku();
    }

    protected function processSuccess(): void
    {
        $requestMetadata = $this->getRequestMetaData();
        $responseData = $this->getResponseData();

        $product = $this->getProduct();

        $productResponseData = $responseData['products'][0];

        $logger = $this->loggerFactory->create();
        $logger->saveProductDataBeforeUpdate($product);

        if (
            $this->isTriedUpdatePrice(
                isset($productResponseData['price']),
                isset($requestMetadata['price'])
            )
        ) {
            $priceUpdateStatus = $productResponseData['price'];
            $requestMetadataPrice = $requestMetadata['price'];
            if (!$priceUpdateStatus) {
                $this->getLogBuffer()->addFail('Price failed to be revised.');
            } else {
                $product->setOnlinePrice($requestMetadataPrice);

                if (array_key_exists('sale_price', $requestMetadata)) {
                    $salesPriceMetadata = $requestMetadata['sale_price'];

                    $product->setOnlineSalePrice($salesPriceMetadata['amount'] ?? null);
                    $product->setOnlineSalePriceStartDate($salesPriceMetadata['start_date'] ?? null);
                    $product->setOnlineSalePriceEndDate($salesPriceMetadata['end_date'] ?? null);
                }
            }
        }

        if (
            $this->isTriedUpdateQty(
                isset($productResponseData['qty']),
                isset($requestMetadata['qty'])
            )
        ) {
            $qtyUpdateStatus = $productResponseData['qty'];
            $requestMetadataQty = $requestMetadata['qty'];
            if (!$qtyUpdateStatus) {
                $this->getLogBuffer()->addFail('Qty failed to be revised.');
            } else {
                $product->setOnlineQty($requestMetadataQty);
            }
        }

        if ($this->isTriedUpdateDetails(isset($productResponseData['details']), isset($requestMetadata['details']))) {
            $detailUpdateStatus = $productResponseData['details'];
            if (!$detailUpdateStatus) {
                $this->getLogBuffer()->addFail('Details failed to be revised.');
            } else {
                $product
                    ->setOnlineBrandId($requestMetadata['details']['brand_id'])
                    ->setOnlineBrandName($requestMetadata['details']['brand_name'])
                    ->setOnlineTitle($requestMetadata['details']['title'])
                    ->setOnlineDescription($requestMetadata['details']['description_hash'])
                    ->setOnlineCategoryName($requestMetadata['details']['category_name'])
                    ->setOnlineCategoryAttributesData($requestMetadata['details']['category_attributes_hash'])
                    ->setOnlineImagesData($requestMetadata['details']['images_hash'])
                    ->setOnlineMpn($requestMetadata['details']['mpn'])
                    ->setOnlineManufacturer($requestMetadata['details']['manufacturer'])
                    ->setOnlineVat($requestMetadata['details']['vat'])
                    ->setOnlineEan($requestMetadata['details']['ean'])
                    ->setOnlineDeliveryData(
                        $requestMetadata['details']['delivery_type'],
                        $requestMetadata['details']['delivery_time'],
                    )
                    ->setOnlineShippingProfileId($requestMetadata['details']['shipping_profile_id'] ?? null)
                    ->setOnlineDeliveryType($requestMetadata['details']['delivery_type']);

                if (
                    array_key_exists('msrp_price', $requestMetadata['details'])
                ) {
                    $product->setOnlineMsrp($requestMetadata['details']['msrp_price']['amount'] ?? null);
                }
            }
        }

        $product->setStatus(\M2E\Otto\Model\Product::STATUS_LISTED, $this->getStatusChanger());

        $product->removeBlockingByError();

        $this->productRepository->save($product);

        $messages = $logger->collectSuccessMessages($product);
        if (empty($messages)) {
            $this->getLogBuffer()->addSuccess('Item was revised');
        }

        foreach ($messages as $message) {
            $this->getLogBuffer()->addSuccess($message);
        }
    }

    private function isTriedUpdatePrice(bool $isPricePresentInResponse, bool $isSendPrice): bool
    {
        return $isPricePresentInResponse && $isSendPrice;
    }

    private function isTriedUpdateQty(bool $isQtyPresentInResponse, bool $isSendQty): bool
    {
        return $isQtyPresentInResponse && $isSendQty;
    }

    private function isTriedUpdateDetails(bool $isDetailsPresentInResponse, bool $isSendDetails): bool
    {
        return $isDetailsPresentInResponse && $isSendDetails;
    }

    public function generateResultMessage(): void
    {
        if (!$this->validateProduct()) {
            return;
        }

        $responseData = $this->getResponseData();

        foreach ($responseData['products'][0]['messages'] ?? [] as $messageData) {
            $this->getLogBuffer()->addFail($messageData['title']);
        }
    }
}
