<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product;

class ActionCalculator
{
    private \M2E\Otto\Model\Magento\Product\RuleFactory $ruleFactory;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Type\Revise\Checker $reviseChecker;

    public function __construct(
        \M2E\Otto\Model\Magento\Product\RuleFactory $ruleFactory,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Type\Revise\Checker $reviseChecker
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->reviseChecker = $reviseChecker;
    }

    public function calculate(\M2E\Otto\Model\Product $product, bool $force, int $change): Action
    {
        if ($product->isStatusNotListed()) {
            return $this->calculateToList($product);
        }

        if ($product->isStatusListed()) {
            return $this->calculateToReviseOrStop($product, $force, $force, $force, $force, $force);
        }

        if ($product->isStatusInactive()) {
            return $this->calculateToRelist($product, $change);
        }

        return Action::createNothing($product);
    }

    public function calculateToList(\M2E\Otto\Model\Product $product): Action
    {
        if (
            !$product->isListable()
            || !$product->isStatusNotListed()
        ) {
            return Action::createNothing($product);
        }

        if (!$this->isNeedListProduct($product)) {
            return Action::createNothing($product);
        }

        $configurator = new \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator();
        $configurator->enableAll();

        return Action::createList($product, $configurator);
    }

    private function isNeedListProduct(\M2E\Otto\Model\Product $product): bool
    {
        $syncPolicy = $product->getSynchronizationTemplate();

        if (!$syncPolicy->isListMode()) {
            return false;
        }

        if (
            $syncPolicy->isListStatusEnabled()
            && !$product->getMagentoProduct()->isStatusEnabled()
        ) {
            return false;
        }

        if (
            $syncPolicy->isListIsInStock()
            && !$product->getMagentoProduct()->isStockAvailability()
        ) {
            return false;
        }

        if (
            $syncPolicy->isListWhenQtyCalculatedHasValue()
            && !$this->isProductHasCalculatedQtyForListRevise(
                $product,
                (int)$syncPolicy->getListWhenQtyCalculatedHasValue()
            )
        ) {
            return false;
        }

        if (
            $syncPolicy->isListAdvancedRulesEnabled()
            && !$this->isListAdvancedRuleMet($product, $syncPolicy)
        ) {
            return false;
        }

        return true;
    }

    private function isProductHasCalculatedQtyForListRevise(
        \M2E\Otto\Model\Product $product,
        int $minQty
    ): bool {
        $productQty = $product->getDataProvider()->getQty()->getValue();

        return $productQty >= $minQty;
    }

    private function isListAdvancedRuleMet(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Template\Synchronization $syncPolicy
    ): bool {
        $ruleModel = $this->ruleFactory
            ->create(
                \M2E\Otto\Model\Template\Synchronization::LIST_ADVANCED_RULES_PREFIX,
                $product->getListing()->getStoreId()
            );
        $ruleModel->loadFromSerialized($syncPolicy->getListAdvancedRulesFilters());

        if ($ruleModel->validate($product->getMagentoProduct()->getProduct())) {
            return true;
        }

        return false;
    }

    // ----------------------------------------

    public function calculateToReviseOrStop(
        \M2E\Otto\Model\Product $product,
        bool $isDetectChangeTitle,
        bool $isDetectChangeDescription,
        bool $isDetectChangeImages,
        bool $isDetectChangeCategories,
        bool $isDetectChangeShippingProfile
    ): Action {
        if (
            !$product->isRevisable()
            && !$product->isStoppable()
        ) {
            return Action::createNothing($product);
        }

        if ($this->isNeedStopProduct($product)) {
            return Action::createStop($product);
        }

        $configurator = new \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator();
        $configurator->disableAll();

        $this->updateConfiguratorAddQty(
            $configurator,
            $product
        );

        $this->updateConfiguratorAddPrice(
            $configurator,
            $product
        );

        $this->updateConfiguratorAddTitle(
            $configurator,
            $product,
            $isDetectChangeTitle,
        );

        $this->updateConfiguratorAddDescription(
            $configurator,
            $product,
            $isDetectChangeDescription,
        );

        $this->updateConfiguratorAddImages(
            $configurator,
            $product,
            $isDetectChangeImages,
        );

        $this->updateConfiguratorAddCategories(
            $configurator,
            $product,
            $isDetectChangeCategories,
        );

        $this->updateConfiguratorAddShippingProfile(
            $configurator,
            $product,
            $isDetectChangeShippingProfile,
        );

        if (empty($configurator->getAllowedDataTypes())) {
            return Action::createNothing($product);
        }

        return Action::createRevise($product, $configurator);
    }

    private function isNeedStopProduct(\M2E\Otto\Model\Product $product): bool
    {
        $syncPolicy = $product->getSynchronizationTemplate();

        if (!$syncPolicy->isStopMode()) {
            return false;
        }

        if (
            $syncPolicy->isStopStatusDisabled()
            && !$product->getMagentoProduct()->isStatusEnabled()
        ) {
            return true;
        }

        if (
            $syncPolicy->isStopOutOfStock()
            && !$product->getMagentoProduct()->isStockAvailability()
        ) {
            return true;
        }

        if (
            $syncPolicy->isStopWhenQtyCalculatedHasValue()
            && $this->isProductHasCalculatedQtyForStop(
                $product,
                (int)$syncPolicy->getStopWhenQtyCalculatedHasValueMin()
            )
        ) {
            return true;
        }

        if (
            $syncPolicy->isStopAdvancedRulesEnabled()
            && $this->isStopAdvancedRuleMet($product, $syncPolicy)
        ) {
            return true;
        }

        return false;
    }

    private function isProductHasCalculatedQtyForStop(
        \M2E\Otto\Model\Product $product,
        int $minQty
    ): bool {
        $productQty = $product->getDataProvider()->getQty()->getValue();

        return $productQty <= $minQty;
    }

    private function isStopAdvancedRuleMet(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Template\Synchronization $syncPolicy
    ): bool {
        $ruleModel = $this->ruleFactory
            ->create(
                \M2E\Otto\Model\Template\Synchronization::STOP_ADVANCED_RULES_PREFIX,
                $product->getListing()->getStoreId()
            );
        $ruleModel->loadFromSerialized($syncPolicy->getStopAdvancedRulesFilters());

        if ($ruleModel->validate($product->getMagentoProduct()->getProduct())) {
            return true;
        }

        return false;
    }

    // ----------------------------------------

    private function updateConfiguratorAddQty(
        \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $configurator,
        \M2E\Otto\Model\Product $product
    ): void {
        $syncPolicy = $product->getSynchronizationTemplate();

        if (
            $syncPolicy->isReviseUpdateQty()
            && $this->isChangedQty($product, $syncPolicy)
        ) {
            $configurator->allowQty();
        }
    }

    private function isChangedQty(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Template\Synchronization $syncPolicy
    ): bool {
        $maxAppliedValue = $syncPolicy->getReviseUpdateQtyMaxAppliedValue();

        $productQty = $product->getDataProvider()->getQty()->getValue();
        $channelQty = $product->getOnlineQty();

        if (
            $syncPolicy->isReviseUpdateQtyMaxAppliedValueModeOn()
            && $productQty > $maxAppliedValue
            && $channelQty > $maxAppliedValue
        ) {
            return false;
        }

        if ($productQty === $channelQty) {
            return false;
        }

        return true;
    }

    private function updateConfiguratorAddPrice(
        \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $configurator,
        \M2E\Otto\Model\Product $product
    ): void {
        $syncPolicy = $product->getSynchronizationTemplate();

        if (
            $syncPolicy->isReviseUpdatePrice()
            && $this->isChangedPrice($product)
        ) {
            $configurator->allowPrice();
        }
    }

    private function isChangedPrice(\M2E\Otto\Model\Product $product): bool
    {
        return $product->getOnlineCurrentPrice() !== $product->getDataProvider()->getPrice()->getValue()->price
            || $this->isChangedSalePrice($product)
            || $this->isChangedMsrp($product);
    }

    private function isChangedSalePrice(\M2E\Otto\Model\Product $product): bool
    {
        $salePriceData = $product->getDataProvider()->getSalePrice()->getValue();

        $productData = [
            'value' => $product->getOnlineSalePrice(),
            'start_date' => $product->getOnlineSalePriceStartDate(),
            'end_date' => $product->getOnlineSalePriceEndDate(),
        ];
        $policyData = [
            'value' => $salePriceData === null ? null : $salePriceData->value,
            'start_date' => $salePriceData === null ? null : $salePriceData->getFormattedStartDate(),
            'end_date' => $salePriceData === null ? null : $salePriceData->getFormattedEndDate(),
        ];

        return $productData !== $policyData;
    }

    private function isChangedMsrp(\M2E\Otto\Model\Product $product): bool
    {
        $msrp = $product->getDataProvider()->getMsrp()->getValue();
        $onlineMsrp = $product->getOnlineMsrp();

        return $msrp !== $onlineMsrp;
    }

    private function updateConfiguratorAddTitle(
        \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $configurator,
        \M2E\Otto\Model\Product $product,
        bool $hasInstructionsForUpdateTitle
    ): void {
        if (!$hasInstructionsForUpdateTitle) {
            return;
        }

        if ($this->reviseChecker->isNeedReviseForTitle($product)) {
            $configurator->allowTitle();

            return;
        }

        $configurator->disallowTitle();
    }

    private function updateConfiguratorAddDescription(
        \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $configurator,
        \M2E\Otto\Model\Product $product,
        bool $hasInstructionsForUpdateDescription
    ): void {
        if (!$hasInstructionsForUpdateDescription) {
            return;
        }

        if ($this->reviseChecker->isNeedReviseForDescription($product)) {
            $configurator->allowDescription();

            return;
        }

        $configurator->disallowDescription();
    }

    private function updateConfiguratorAddImages(
        \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $configurator,
        \M2E\Otto\Model\Product $product,
        bool $hasInstructionsForUpdateImages
    ): void {
        if (!$hasInstructionsForUpdateImages) {
            return;
        }

        if ($this->reviseChecker->isNeedReviseForImages($product)) {
            $configurator->allowImages();

            return;
        }

        $configurator->disallowImages();
    }

    private function updateConfiguratorAddCategories(
        \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $configurator,
        \M2E\Otto\Model\Product $product,
        bool $hasInstructionsForUpdateCategories
    ): void {
        if (!$hasInstructionsForUpdateCategories) {
            return;
        }

        if (
            $this->reviseChecker->isNeedReviseForCategories($product)
            || $this->reviseChecker->isNeedReviseForBrand($product)
            || $this->reviseChecker->isNeedReviseForMpnOrManufacturer($product)
        ) {
            $configurator->allowCategories();

            return;
        }

        $configurator->disallowCategories();
    }

    private function updateConfiguratorAddShippingProfile(
        \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $configurator,
        \M2E\Otto\Model\Product $product,
        bool $hasInstructionsForUpdateShippingProfile
    ): void {
        if (!$hasInstructionsForUpdateShippingProfile) {
            return;
        }

        if (
            $this->reviseChecker->isNeedReviseForShippingProfile($product)
        ) {
            $configurator->allowShippingProfile();

            return;
        }

        $configurator->disallowShippingProfile();
    }

    // ----------------------------------------

    public function calculateToRelist(\M2E\Otto\Model\Product $product, int $changer): Action
    {
        if (!$product->isRelistable()) {
            return Action::createNothing($product);
        }

        if (!$this->isNeedRelistProduct($product, $changer)) {
            return Action::createNothing($product);
        }

        $configurator = new \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator();
        $configurator->enableAll();

        return Action::createRelist($product, $configurator);
    }

    private function isNeedRelistProduct(\M2E\Otto\Model\Product $product, int $changer): bool
    {
        $syncPolicy = $product->getSynchronizationTemplate();

        if (!$syncPolicy->isRelistMode()) {
            return false;
        }

        if (
            $product->isStatusInactive()
            && $syncPolicy->isRelistFilterUserLock()
            && $product->isStatusChangerUser()
            && $changer !== \M2E\Otto\Model\Product::STATUS_CHANGER_USER
        ) {
            return false;
        }

        if (
            $syncPolicy->isRelistStatusEnabled()
            && !$product->getMagentoProduct()->isStatusEnabled()
        ) {
            return false;
        }

        if (
            $syncPolicy->isRelistIsInStock()
            && !$product->getMagentoProduct()->isStockAvailability()
        ) {
            return false;
        }

        if (
            $syncPolicy->isRelistWhenQtyCalculatedHasValue()
            && !$this->isProductHasCalculatedQtyForListRevise(
                $product,
                (int)$syncPolicy->getListWhenQtyCalculatedHasValue()
            )
        ) {
            return false;
        }

        if (
            $syncPolicy->isReviseUpdatePrice()
            && $this->isChangedPrice($product)
        ) {
            return true;
        }

        if (
            $syncPolicy->isRelistAdvancedRulesEnabled()
            && !$this->isRelistAdvancedRuleMet($product, $syncPolicy)
        ) {
            return false;
        }

        return true;
    }

    private function isRelistAdvancedRuleMet(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Template\Synchronization $syncPolicy
    ): bool {
        $ruleModel = $this->ruleFactory
            ->create(
                \M2E\Otto\Model\Template\Synchronization::RELIST_ADVANCED_RULES_PREFIX,
                $product->getListing()->getStoreId()
            );
        $ruleModel->loadFromSerialized($syncPolicy->getRelistAdvancedRulesFilters());

        if ($ruleModel->validate($product->getMagentoProduct()->getProduct())) {
            return true;
        }

        return false;
    }
}
