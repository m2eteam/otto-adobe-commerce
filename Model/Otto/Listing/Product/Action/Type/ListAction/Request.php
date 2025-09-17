<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Type\ListAction;

use M2E\Otto\Model\Product\DataProvider\Category\Attribute as CategoryAttribute;

class Request extends \M2E\Otto\Model\Otto\Listing\Product\Action\AbstractRequest
{
    use \M2E\Otto\Model\Otto\Listing\Product\Action\RequestTrait;

    private array $metadata = [];

    public function getActionData(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $actionConfigurator,
        array $params
    ): array {
        $dataProvider = $product->getDataProvider();

        $categoryData = $dataProvider->getCategory()->getValue();
        $priceData = $dataProvider->getPrice()->getValue();
        $deliveryData = $dataProvider->getDelivery()->getValue();
        $detailsData = $dataProvider->getDetails()->getValue();
        $brandData = $dataProvider->getBrand()->getValue();
        $imagesData = $dataProvider->getImages()->getValue();
        $salePriceData = $dataProvider->getSalePrice()->getValue();
        $msrp = $dataProvider->getMsrp()->getValue();

        $request = [
            'sku' => $product->getMagentoProduct()->getSku(),
            'brand_id' => $brandData->id,
            'product_reference' => $dataProvider->getProductReference()->getValue(),
            'ean' => $dataProvider->getEan()->getValue(),
            'price' => $priceData->price,
            'qty' => $dataProvider->getQty()->getValue(),
            'currency_code' => $priceData->currencyCode,
            'product_line' => $dataProvider->getTitle()->getValue(),
            'description' => $dataProvider->getDescription()->getValue()->description,
            'category' => $categoryData->title,
            'attributes' => array_map(
                static function (CategoryAttribute $attribute) {
                    return [
                        'name' => $attribute->name,
                        'values' => $attribute->values,
                    ];
                },
                $categoryData->attributes
            ),
            'media_assets' => array_map(
                static function (\M2E\Otto\Model\Product\DataProvider\Images\Image $image) {
                    return [
                        'type' => $image->type,
                        'location' => $image->location,
                    ];
                },
                $dataProvider->getImages()->getValue()->set
            ),
            'vat' => $dataProvider->getVat()->getValue(),
            'mpn' => $detailsData->mpn,
            'manufacturer' => $detailsData->manufacturer,
            'bullet_points' => $detailsData->bulletPoints,
            'sale_price' => null,
            'msrp_price' => null,
        ];

        if ($salePriceData !== null) {
            $request['sale_price'] = [
                'amount' => $salePriceData->value,
                'start_date' => $salePriceData->getFormattedStartDate(),
                'end_date' => $salePriceData->getFormattedEndDate(),
            ];
        }

        if ($msrp !== null) {
            $request['msrp_price']['amount'] = $msrp;
        }

        if ($deliveryData->shippingProfileId !== null) {
            $request['shipping_profile_id'] = $deliveryData->shippingProfileId;
        } else {
            $request['delivery_type'] = $deliveryData->deliveryType;
            $request['delivery_time'] = $deliveryData->deliveryTime;
        }

        $this->metadata = [
            'qty' => $request['qty'],
            'brand_name' => $brandData->name,
            'brand_id' => $request['brand_id'],
            'price' => $request['price'],
            'title' => $request['product_line'],
            'description_hash' => $dataProvider->getDescription()->getValue()->hash,
            'vat' => $request['vat'],
            'ean' => $request['ean'],
            'product_reference' => $request['product_reference'],
            'delivery_type' => $deliveryData->deliveryType,
            'delivery_time' => $deliveryData->deliveryTime,
            'category_name' => $request['category'],
            'category_attributes_hash' => $categoryData->attributesHash,
            'mpn' => $request['mpn'],
            'manufacturer' => $request['manufacturer'],
            'images_hash' => $imagesData->imagesHash,
            'sale_price' => null,
            'msrp_price' => null,
        ];

        if ($deliveryData->shippingProfileId !== null) {
            $this->metadata['shipping_profile_id'] = $deliveryData->shippingProfileId;
        }

        if ($salePriceData !== null) {
            $this->metadata['sale_price'] = [
                'amount' => $salePriceData->value,
                'start_date' => $salePriceData->getFormattedStartDate(),
                'end_date' => $salePriceData->getFormattedEndDate(),
            ];
        }

        if ($msrp !== null) {
            $this->metadata['msrp_price'] = [
                'amount' => $msrp,
            ];
        }

        $this->processDataProviderLogs($dataProvider);

        return [
            'product' => $request,
        ];
    }

    protected function getActionMetadata(): array
    {
        return $this->metadata;
    }
}
