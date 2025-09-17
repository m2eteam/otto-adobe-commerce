<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Template\SellingFormat;

class Messages extends \M2E\Otto\Block\Adminhtml\Template\AbstractMessages
{
    private string $currencyCode;
    private \M2E\Otto\Model\Currency $currency;
    private \Magento\Store\Model\Store $store;
    private \M2E\Core\Helper\Magento\Store $magentoStoreHelper;

    public function __construct(
        string $currencyCode,
        \M2E\Otto\Model\Currency $currency,
        \M2E\Core\Helper\Magento\Store $magentoStoreHelper,
        \Magento\Store\Model\Store $store,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        $this->currencyCode = $currencyCode;
        $this->currency = $currency;
        $this->store = $store;
        $this->magentoStoreHelper = $magentoStoreHelper;
        parent::__construct($context, $data);
    }

    public function getMessages(): array
    {
        $message = $this->getCurrencyConversionMessage();
        if ($message === null) {
            return [];
        }

        return [$message];
    }

    private function getCurrencyConversionMessage(): ?string
    {
        if (!$this->canDisplayCurrencyConversionMessage()) {
            return null;
        }

        $isAllowedCurrencyForStore = $this->currency
            ->isAllowed($this->currency, $this->store);

        if (!$isAllowedCurrencyForStore) {
            $currencySetupUrl = $this->getUrl(
                'admin/system_config/edit',
                [
                    'section' => 'currency',
                    'website' => !$this->isDefaultStore() ? $this->store->getWebsite()->getId() : null,
                    'store' => !$this->isDefaultStore() ? $this->store->getId() : null,
                ]
            );

            return
                (string)__(
                    'Currency "%currency_code" is not allowed in <a href="%url" target="_blank">Currency Setup</a> '
                    . 'for Store View "%store_path" of your Magento. '
                    . 'Currency conversion will not be performed.',
                    [
                        'currency_code' => $this->currencyCode,
                        'url' => $currencySetupUrl,
                        'store_path' => $this->_escaper
                            ->escapeHtml($this->magentoStoreHelper->getStorePath($this->store->getId())),
                    ]
                );
        }

        $currencyRate = $this->currency
            ->getConvertRateFromBase($this->currencyCode, $this->store);

        if ($currencyRate == 0) {
            return
                (string)__(
                    'There is no rate for "%currency_from-%currency_to" in'
                    . ' <a href="%url" target="_blank">Manage Currency Rates</a> of your Magento.'
                    . ' Currency conversion will not be performed.',
                    [
                        'currency_from' => $this->store->getBaseCurrencyCode(),
                        'currency_to' => $this->currencyCode,
                        'url' => $this->getUrl('adminhtml/system_currency'),
                    ]
                );
        }

        $message =
            (string)__(
                'There is a rate %rate for "%currency_from-%currency_to" in'
                . ' <a href="%url" target="_blank">Manage Currency Rates</a> of your Magento.'
                . ' Currency conversion will be performed automatically.',
                [
                    'rate' => $currencyRate,
                    'currency_from' => $this->store->getBaseCurrencyCode(),
                    'currency_to' => $this->currencyCode,
                    'url' => $this->getUrl('adminhtml/system_currency'),
                ]
            );

        return '<span style="color: #3D6611 !important;">' . $message . '</span>';
    }

    private function canDisplayCurrencyConversionMessage(): bool
    {
        if ($this->store->getId() === null) {
            return false;
        }

        if ($this->currency->isBase($this->currency, $this->store)) {
            return false;
        }

        return true;
    }

    private function isDefaultStore(): bool
    {
        return (int)$this->store->getId() === \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }
}
