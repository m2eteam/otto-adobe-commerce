<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\DataProvider;

class CategoryProvider implements DataBuilderInterface
{
    use DataBuilderHelpTrait;

    public const NICK = 'Category';

    private \M2E\Otto\Model\Magento\Product\Attribute\RetrieveValueFactory $magentoAttributeRetriever;

    public function __construct(
        \M2E\Otto\Model\Magento\Product\Attribute\RetrieveValueFactory $magentoAttributeRetriever
    ) {
        $this->magentoAttributeRetriever = $magentoAttributeRetriever;
    }

    public function getCategory(\M2E\Otto\Model\Product $product): \M2E\Otto\Model\Product\DataProvider\Category\Value
    {
        $category = $product->getCategoryTemplate();

        $productAttributeData = $this->getProductAttributeData($product, $category);

        $attributes = [];
        foreach ($productAttributeData as $attributeName => $values) {
            if (empty($values)) {
                continue;
            }

            $attributes[] = new \M2E\Otto\Model\Product\DataProvider\Category\Attribute((string)$attributeName, $values);
        }

        $attributesHash = $this->generateAttributesHash($attributes);

        return new \M2E\Otto\Model\Product\DataProvider\Category\Value($category->getTitle(), $attributes, $attributesHash);
    }

    private function getProductAttributeData(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Category $category
    ): array {
        $result = [];

        $magentoAttributeRetriever = $this->magentoAttributeRetriever->create($product->getMagentoProduct());
        foreach ($category->getProductAttributes() as $attribute) {
            if ($attribute->isValueModeNone()) {
                $result[$attribute->getAttributeName()] = [];
                continue;
            }

            if ($attribute->isValueModeRecommended()) {
                foreach ($attribute->getRecommendedValue() as $valueId) {
                    $result[$attribute->getAttributeName()][] = $valueId;
                }

                continue;
            }

            if ($attribute->isValueModeCustomValue()) {
                $attributeVal = $attribute->getCustomValue();
                if (!empty($attributeVal)) {
                    $result[$attribute->getAttributeName()][] = $attributeVal;
                }

                continue;
            }

            if ($attribute->isValueModeCustomAttribute()) {
                $attributeVal = $magentoAttributeRetriever->tryRetrieve(
                    $attribute->getCustomAttributeValue(),
                    'Category Attribute'
                );
                if ($attributeVal !== null) {
                    $result[$attribute->getAttributeName()][] = $attributeVal;
                }

                $this->addNotFoundAttributesToWarning($magentoAttributeRetriever);
            }
        }

        return $result;
    }

    /**
     * @param \M2E\Otto\Model\Product\DataProvider\Category\Attribute[] $attributes
     *
     * @return string
     */
    private function generateAttributesHash(array $attributes): string
    {
        $flatAttributes = [];
        foreach ($attributes as $attribute) {
            $flatAttributes[$attribute->name] = implode(',', $attribute->values);
        }

        ksort($flatAttributes);

        return sha1(json_encode($flatAttributes));
    }
}
