<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Account;

class Switcher extends \M2E\Otto\Block\Adminhtml\Switcher
{
    /** @var string */
    protected $paramName = 'account';
    private \M2E\Otto\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\Otto\Model\Account\Repository $accountRepository,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->accountRepository = $accountRepository;
    }

    public function getLabel(): string
    {
        return (string)__('Account');
    }

    protected function loadItems(): void
    {
        $accounts = $this->accountRepository->getAll();
        if (count($accounts) < 2) {
            $this->hasDefaultOption = false;
            $this->setIsDisabled();
        }

        $items = [];
        foreach ($accounts as $account) {
            $accountTitle = $this->filterManager->truncate(
                $account->getTitle(),
                ['length' => 15]
            );

            $items['accounts']['value'][] = [
                'value' => $account->getId(),
                'label' => $accountTitle,
            ];
        }

        $this->items = $items;
    }

    private function setIsDisabled(): void
    {
        $this->setData('is_disabled', true);
    }
}