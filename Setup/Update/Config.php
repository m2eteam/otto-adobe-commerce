<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update;

class Config implements \M2E\Core\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            'y24_m05' => [
                \M2E\Otto\Setup\Update\y24_m05\AddStatusChangerColumnToScheduledAction::class,
            ],
            'y24_m06' => [
                \M2E\Otto\Setup\Update\y24_m06\AddDescriptionColumnsToProductTable::class,
                \M2E\Otto\Setup\Update\y24_m06\AddDescriptionTemplateIdToListing::class,
                \M2E\Otto\Setup\Update\y24_m06\AddDescriptionTemplateTable::class,
                \M2E\Otto\Setup\Update\y24_m06\AddCategoryGroupDictionaryTable::class,
                \M2E\Otto\Setup\Update\y24_m06\AddCategoryDictionaryTable::class,
                \M2E\Otto\Setup\Update\y24_m06\AddAttributeDictionaryTable::class,
                \M2E\Otto\Setup\Update\y24_m06\AddCategoryTable::class,
                \M2E\Otto\Setup\Update\y24_m06\ListingWizard::class,
                \M2E\Otto\Setup\Update\y24_m06\AddProductUrl::class,
                \M2E\Otto\Setup\Update\y24_m06\AddMagentoShipmentIdColumnToOrderChange::class,
                \M2E\Otto\Setup\Update\y24_m06\RemoveListingProductAddIds::class,
                \M2E\Otto\Setup\Update\y24_m06\AddAttributeTable::class,
                \M2E\Otto\Setup\Update\y24_m06\AddBrandTable::class,
                \M2E\Otto\Setup\Update\y24_m06\AddShippingColumnsToProductTable::class,
                \M2E\Otto\Setup\Update\y24_m06\AddShippingTemplateIdToListing::class,
                \M2E\Otto\Setup\Update\y24_m06\AddShippingTemplateTable::class,
                \M2E\Otto\Setup\Update\y24_m06\AddBulletPointsToDescPolicy::class,
                \M2E\Otto\Setup\Update\y24_m06\AddOnlineSkuToProductTable::class,
                \M2E\Otto\Setup\Update\y24_m06\RemoveListingProductConfigurations::class,
            ],
            'y24_m07' => [
                \M2E\Otto\Setup\Update\y24_m07\AddMoinColumnsToProductTable::class,
            ],
            'y24_m08' => [
                \M2E\Otto\Setup\Update\y24_m08\DropImageAndImageRelationTables::class,
                \M2E\Otto\Setup\Update\y24_m08\RefactorCategoryTable::class,
                \M2E\Otto\Setup\Update\y24_m08\UpdateProductStatus::class,
            ],
            'y24_m09' => [
                \M2E\Otto\Setup\Update\y24_m09\AddColumnsToShippingTemplateTable::class,
                \M2E\Otto\Setup\Update\y24_m09\RemoveUniqueConstraintFromEanColumn::class,
                \M2E\Otto\Setup\Update\y24_m09\AddOnlineColumnsToProductTable::class,
                \M2E\Otto\Setup\Update\y24_m09\FixTablesStructure::class,
                \M2E\Otto\Setup\Update\y24_m09\AddIsIncompleteColumns::class,
            ],
            'y24_m11' => [
                \M2E\Otto\Setup\Update\y24_m11\AddShippingProfiles::class,
                \M2E\Otto\Setup\Update\y24_m11\AddAttributeMapping::class,
            ],
            'y25_m01' => [
                \M2E\Otto\Setup\Update\y25_m01\AddTrackDirectDatabaseChanges::class,
                \M2E\Otto\Setup\Update\y25_m01\AddMarketplaceErrorsToProduct::class,
                \M2E\Otto\Setup\Update\y25_m01\AddExternalChangeTable::class,
            ],
            'y25_m02' => [
                \M2E\Otto\Setup\Update\y25_m02\MigrateLicenseAndRegistrationUserToCore::class,
                \M2E\Otto\Setup\Update\y25_m02\MigrateConfigToCore::class,
                \M2E\Otto\Setup\Update\y25_m02\MigrateRegistryToCore::class,
                \M2E\Otto\Setup\Update\y25_m02\RemoveServerHost::class,
                \M2E\Otto\Setup\Update\y25_m02\RemoveOldCronValues::class,
            ],
            'y25_m03' => [
                \M2E\Otto\Setup\Update\y25_m03\CheckConfigs::class,
            ],
            'y25_m04' => [
                \M2E\Otto\Setup\Update\y25_m04\MigrateAttributeMappingToCore::class,
            ],
            'y25_m06' => [
                \M2E\Otto\Setup\Update\y25_m06\RemoveReferencesOfPolicyFromProduct::class,
            ],
            'y25_m08' => [
                \M2E\Otto\Setup\Update\y25_m08\AddSalePrice::class,
                \M2E\Otto\Setup\Update\y25_m08\RemoveOnlineCurrency::class,
                \M2E\Otto\Setup\Update\y25_m08\AddMsrp::class,
            ],
        ];
    }
}
