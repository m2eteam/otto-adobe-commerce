<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\DataProvider;

use M2E\Otto\Model\Template\SellingFormat as SellingFormatPolicy;

class MsrpProvider implements DataBuilderInterface
{
    use DataBuilderHelpTrait;

    public const NICK = 'Msrp';

    private \M2E\Otto\Model\Currency $currency;
    private \M2E\Otto\Model\Magento\Product\Attribute\RetrieveValueFactory $magentoAttributeRetriever;

    public function __construct(
        \M2E\Otto\Model\Currency $currency,
        \M2E\Otto\Model\Magento\Product\Attribute\RetrieveValueFactory $magentoAttributeRetriever
    ) {
        $this->currency = $currency;
        $this->magentoAttributeRetriever = $magentoAttributeRetriever;
    }

    public function getMsrp(\M2E\Otto\Model\Product $product): ?float
    {
        $sellingTemplate = $product->getSellingFormatTemplate();

        $msrp = $this->retrieveMsrp($product, $sellingTemplate);
        if (empty($msrp)) {
            return null;
        }

        return $msrp;
    }

    private function retrieveMsrp(
        \M2E\Otto\Model\Product $product,
        SellingFormatPolicy $sellingTemplate
    ): ?float {
        if ($sellingTemplate->getMsrpMode() === SellingFormatPolicy::MSRP_MODE_NONE) {
            return null;
        }

        $attribute = $sellingTemplate->getMsrpAttribute();
        if (empty($attribute)) {
            return null;
        }

        $value = (float)$this->retrieveMagentoAttributeValue($product, (string)__('Msrp'), $attribute);
        if (empty($value)) {
            return null;
        }

        return round(
            (float)$this->currency->convertPrice(
                $value,
                \M2E\Otto\Model\Currency::CURRENCY_EUR,
                $product->getListing()->getStoreId()
            ),
            2
        );
    }

    private function retrieveMagentoAttributeValue(
        \M2E\Otto\Model\Product $product,
        string $attributeTitle,
        string $attributeCode
    ): ?string {
        $attributeRetriever = $this->magentoAttributeRetriever->create(
            $attributeTitle,
            $product->getMagentoProduct()
        );
        $attributeVal = $attributeRetriever->tryRetrieve($attributeCode);

        if ($attributeVal === null) {
            $this->addNotFoundAttributesToWarning($attributeRetriever);

            return null;
        }

        return $attributeVal;
    }
}
