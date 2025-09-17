<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product;

class DataProvider
{
    /** @var \M2E\Otto\Model\Product\DataProvider\DataBuilderInterface[] */
    private array $dataBuilders = [];

    /** @var \M2E\Otto\Model\Product\DataProvider\AbstractResult[] */
    private array $results = [];

    /** @var \M2E\Otto\Model\Product\DataProvider\Factory */
    private \M2E\Otto\Model\Product\DataProvider\Factory $dataBuilderFactory;

    private \M2E\Otto\Model\Product $product;

    public function __construct(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Product\DataProvider\Factory $dataBuilderFactory
    ) {
        $this->product = $product;
        $this->dataBuilderFactory = $dataBuilderFactory;
    }

    // ----------------------------------------

    public function getPrice(): DataProvider\Price\Result
    {
        if ($this->hasResult(DataProvider\PriceProvider::NICK)) {
            /** @var DataProvider\Price\Result */
            return $this->getResult(DataProvider\PriceProvider::NICK);
        }

        /** @var \M2E\Otto\Model\Product\DataProvider\PriceProvider $builder */
        $builder = $this->getBuilder(\M2E\Otto\Model\Product\DataProvider\PriceProvider::NICK);

        $value = $builder->getPrice($this->product);

        $result = DataProvider\Price\Result::success($value);

        $this->addResult(DataProvider\PriceProvider::NICK, $result);

        return $result;
    }

    public function getSalePrice(): DataProvider\SalePrice\Result
    {
        if ($this->hasResult(DataProvider\SalePriceProvider::NICK)) {
            /** @var DataProvider\SalePrice\Result */
            return $this->getResult(DataProvider\SalePriceProvider::NICK);
        }

        /** @var \M2E\Otto\Model\Product\DataProvider\SalePriceProvider $builder */
        $builder = $this->getBuilder(\M2E\Otto\Model\Product\DataProvider\SalePriceProvider::NICK);

        $value = $builder->getSalePrice($this->product);

        $result = DataProvider\SalePrice\Result::success($value);

        $this->addResult(DataProvider\SalePriceProvider::NICK, $result);

        return $result;
    }

    public function getMsrp(): DataProvider\Msrp\Result
    {
        if ($this->hasResult(DataProvider\MsrpProvider::NICK)) {
            /** @var DataProvider\Msrp\Result */
            return $this->getResult(DataProvider\MsrpProvider::NICK);
        }

        /** @var \M2E\Otto\Model\Product\DataProvider\MsrpProvider $builder */
        $builder = $this->getBuilder(\M2E\Otto\Model\Product\DataProvider\MsrpProvider::NICK);

        $value = $builder->getMsrp($this->product);

        $result = DataProvider\Msrp\Result::success($value);

        $this->addResult(DataProvider\MsrpProvider::NICK, $result);

        return $result;
    }

    public function getQty(): DataProvider\Qty\Result
    {
        if ($this->hasResult(DataProvider\QtyProvider::NICK)) {
            /** @var DataProvider\Qty\Result */
            return $this->getResult(DataProvider\QtyProvider::NICK);
        }

        /** @var \M2E\Otto\Model\Product\DataProvider\QtyProvider $builder */
        $builder = $this->getBuilder(\M2E\Otto\Model\Product\DataProvider\QtyProvider::NICK);

        $value = $builder->getQty($this->product);

        $result = DataProvider\Qty\Result::success($value, $builder->getWarningMessages());

        $this->addResult(DataProvider\QtyProvider::NICK, $result);

        return $result;
    }

    public function getBrand(): DataProvider\Brand\Result
    {
        if ($this->hasResult(DataProvider\BrandProvider::NICK)) {
            /** @var DataProvider\Brand\Result */
            return $this->getResult(DataProvider\BrandProvider::NICK);
        }

        /** @var \M2E\Otto\Model\Product\DataProvider\BrandProvider $builder */
        $builder = $this->getBuilder(\M2E\Otto\Model\Product\DataProvider\BrandProvider::NICK);

        $value = $builder->getBrand($this->product);
        if ($value === null) {
            $result = DataProvider\Brand\Result::error($builder->getWarningMessages());
        } else {
            $result = DataProvider\Brand\Result::success($value);
        }

        $this->addResult(DataProvider\BrandProvider::NICK, $result);

        return $result;
    }

    public function getEan(): DataProvider\Ean\Result
    {
        if ($this->hasResult(DataProvider\EanProvider::NICK)) {
            /** @var DataProvider\Ean\Result */
            return $this->getResult(DataProvider\EanProvider::NICK);
        }

        /** @var \M2E\Otto\Model\Product\DataProvider\EanProvider $builder */
        $builder = $this->getBuilder(\M2E\Otto\Model\Product\DataProvider\EanProvider::NICK);

        $value = $builder->getEan($this->product);
        if ($value === null) {
            $result = DataProvider\Ean\Result::error($builder->getWarningMessages());
        } else {
            $result = DataProvider\Ean\Result::success($value);
        }

        $this->addResult(DataProvider\EanProvider::NICK, $result);

        return $result;
    }

    public function getTitle(): DataProvider\Title\Result
    {
        if ($this->hasResult(DataProvider\TitleProvider::NICK)) {
            /** @var DataProvider\Title\Result */
            return $this->getResult(DataProvider\TitleProvider::NICK);
        }

        /** @var \M2E\Otto\Model\Product\DataProvider\TitleProvider $builder */
        $builder = $this->getBuilder(\M2E\Otto\Model\Product\DataProvider\TitleProvider::NICK);

        $title = $builder->getTitle($this->product);

        $result = DataProvider\Title\Result::success($title);

        $this->addResult(DataProvider\TitleProvider::NICK, $result);

        return $result;
    }

    public function getDescription(): DataProvider\Description\Result
    {
        if ($this->hasResult(DataProvider\DescriptionProvider::NICK)) {
            /** @var DataProvider\Description\Result */
            return $this->getResult(DataProvider\DescriptionProvider::NICK);
        }

        /** @var \M2E\Otto\Model\Product\DataProvider\DescriptionProvider $builder */
        $builder = $this->getBuilder(\M2E\Otto\Model\Product\DataProvider\DescriptionProvider::NICK);

        $value = $builder->getDescription($this->product);

        $result = DataProvider\Description\Result::success($value);

        $this->addResult(DataProvider\DescriptionProvider::NICK, $result);

        return $result;
    }

    public function getCategory(): DataProvider\Category\Result
    {
        if ($this->hasResult(DataProvider\CategoryProvider::NICK)) {
            /** @var DataProvider\Category\Result */
            return $this->getResult(DataProvider\CategoryProvider::NICK);
        }

        /** @var \M2E\Otto\Model\Product\DataProvider\CategoryProvider $builder */
        $builder = $this->getBuilder(\M2E\Otto\Model\Product\DataProvider\CategoryProvider::NICK);

        $value = $builder->getCategory($this->product);

        if ($value === null) {
            $result = DataProvider\Category\Result::error($builder->getWarningMessages());
        } else {
            $result = DataProvider\Category\Result::success($value);
        }

        $this->addResult(DataProvider\CategoryProvider::NICK, $result);

        return $result;
    }

    public function getImages(): DataProvider\Images\Result
    {
        if ($this->hasResult(DataProvider\ImagesProvider::NICK)) {
            /** @var DataProvider\Images\Result */
            return $this->getResult(DataProvider\ImagesProvider::NICK);
        }

        /** @var \M2E\Otto\Model\Product\DataProvider\ImagesProvider $builder */
        $builder = $this->getBuilder(\M2E\Otto\Model\Product\DataProvider\ImagesProvider::NICK);

        $value = $builder->getImages($this->product);

        $result = DataProvider\Images\Result::success($value);

        $this->addResult(DataProvider\ImagesProvider::NICK, $result);

        return $result;
    }

    public function getVat(): DataProvider\Vat\Result
    {
        if ($this->hasResult(DataProvider\VatProvider::NICK)) {
            /** @var DataProvider\Vat\Result */
            return $this->getResult(DataProvider\VatProvider::NICK);
        }

        /** @var \M2E\Otto\Model\Product\DataProvider\VatProvider $builder */
        $builder = $this->getBuilder(\M2E\Otto\Model\Product\DataProvider\VatProvider::NICK);

        $value = $builder->getVat();

        $result = DataProvider\Vat\Result::success($value);

        $this->addResult(DataProvider\VatProvider::NICK, $result);

        return $result;
    }

    public function getDelivery(): DataProvider\Delivery\Result
    {
        if ($this->hasResult(DataProvider\DeliveryProvider::NICK)) {
            /** @var DataProvider\Delivery\Result */
            return $this->getResult(DataProvider\DeliveryProvider::NICK);
        }

        /** @var \M2E\Otto\Model\Product\DataProvider\DeliveryProvider $builder */
        $builder = $this->getBuilder(\M2E\Otto\Model\Product\DataProvider\DeliveryProvider::NICK);

        $value = $builder->getDelivery($this->product);

        if ($value === null) {
            $result = DataProvider\Delivery\Result::error($builder->getWarningMessages());
        } else {
            $result = DataProvider\Delivery\Result::success($value);
        }

        $this->addResult(DataProvider\DeliveryProvider::NICK, $result);

        return $result;
    }

    public function getDetails(): DataProvider\Details\Result
    {
        if ($this->hasResult(DataProvider\DetailsProvider::NICK)) {
            /** @var DataProvider\Details\Result */
            return $this->getResult(DataProvider\DetailsProvider::NICK);
        }

        /** @var \M2E\Otto\Model\Product\DataProvider\DetailsProvider $builder */
        $builder = $this->getBuilder(\M2E\Otto\Model\Product\DataProvider\DetailsProvider::NICK);

        $value = $builder->getDetails($this->product);

        if ($value === null) {
            $result = DataProvider\Details\Result::error($builder->getWarningMessages());
        } else {
            $result = DataProvider\Details\Result::success($value);
        }

        $this->addResult(DataProvider\DetailsProvider::NICK, $result);

        return $result;
    }

    public function getProductReference(): DataProvider\ProductReference\Result
    {
        if ($this->hasResult(DataProvider\ProductReference::NICK)) {
            /** @var DataProvider\ProductReference\Result */
            return $this->getResult(DataProvider\ProductReference::NICK);
        }

        /** @var \M2E\Otto\Model\Product\DataProvider\ProductReference $builder */
        $builder = $this->getBuilder(\M2E\Otto\Model\Product\DataProvider\ProductReference::NICK);

        $value = $builder->generate($this->product);

        $result = DataProvider\ProductReference\Result::success($value);

        $this->addResult(DataProvider\ProductReference::NICK, $result);

        return $result;
    }

    // ----------------------------------------

    /**
     * @return string[]
     */
    public function getLogs(): array
    {
        $result = [];
        foreach ($this->dataBuilders as $dataBuilder) {
            $message = $dataBuilder->getWarningMessages();
            if (empty($message)) {
                continue;
            }

            array_push($result, ...$message);
        }

        return $result;
    }

    // ----------------------------------------

    private function getBuilder(
        string $nick
    ): \M2E\Otto\Model\Product\DataProvider\DataBuilderInterface {
        if (isset($this->dataBuilders[$nick])) {
            return $this->dataBuilders[$nick];
        }

        return $this->dataBuilders[$nick] = $this->dataBuilderFactory->create($nick);
    }

    private function addResult(string $builderNick, DataProvider\AbstractResult $result): void
    {
        $this->results[$builderNick] = $result;
    }

    private function hasResult(string $builderNick): bool
    {
        return isset($this->results[$builderNick]);
    }

    private function getResult(string $builderNick): \M2E\Otto\Model\Product\DataProvider\AbstractResult
    {
        return $this->results[$builderNick];
    }
}
