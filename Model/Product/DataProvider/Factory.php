<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\DataProvider;

class Factory
{
    private const ALLOWED_BUILDERS = [
        PriceProvider::NICK => PriceProvider::class,
        QtyProvider::NICK => QtyProvider::class,
        BrandProvider::NICK => BrandProvider::class,
        ImagesProvider::NICK => ImagesProvider::class,
        DescriptionProvider::NICK => DescriptionProvider::class,
        CategoryProvider::NICK => CategoryProvider::class,
        DeliveryProvider::NICK => DeliveryProvider::class,
        VatProvider::NICK => VatProvider::class,
        TitleProvider::NICK => TitleProvider::class,
        EanProvider::NICK => EanProvider::class,
        DetailsProvider::NICK => DetailsProvider::class,
        ProductReference::NICK => ProductReference::class,
        SalePriceProvider::NICK => SalePriceProvider::class,
        MsrpProvider::NICK => MsrpProvider::class,
    ];

    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(string $nick): DataBuilderInterface
    {
        if (!isset(self::ALLOWED_BUILDERS[$nick])) {
            throw new \M2E\Otto\Model\Exception\Logic(sprintf('Unknown builder - %s', $nick));
        }

        /** @var DataBuilderInterface */
        return $this->objectManager->create(self::ALLOWED_BUILDERS[$nick]);
    }
}
