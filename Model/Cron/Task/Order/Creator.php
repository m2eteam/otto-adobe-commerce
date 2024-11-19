<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Cron\Task\Order;

class Creator extends \Magento\Framework\DataObject
{
    private bool $isValidateAccountCreateDate = true;

    private \M2E\Otto\Model\Synchronization\LogService $syncLogService;
    private \M2E\Otto\Model\Otto\Order\BuilderFactory $orderBuilderFactory;
    private \M2E\Otto\Helper\Module\Exception $exceptionHelper;
    private \M2E\Otto\Model\Order\Repository $orderRepository;

    public function __construct(
        \M2E\Otto\Model\Synchronization\LogService $syncLogService,
        \M2E\Otto\Model\Otto\Order\BuilderFactory $orderBuilderFactory,
        \M2E\Otto\Model\Order\Repository $orderRepository,
        \M2E\Otto\Helper\Module\Exception $exceptionHelper,
        array $data = []
    ) {
        parent::__construct($data);
        $this->orderBuilderFactory = $orderBuilderFactory;
        $this->exceptionHelper = $exceptionHelper;
        $this->orderRepository = $orderRepository;
        $this->syncLogService = $syncLogService;
    }

    public function setValidateAccountCreateDate(bool $mode): void
    {
        $this->isValidateAccountCreateDate = $mode;
    }

    /**
     * @return \M2E\Otto\Model\Order[]
     * @throws \M2E\Otto\Model\Exception\Logic
     * @throws \Exception
     */
    public function processOttoOrders(
        \M2E\Otto\Model\Account $account,
        array $ordersData
    ): array {

        $accountCreateDate = clone $account->getCreateData();
        $boundaryCreationDate = \M2E\Otto\Helper\Date::createCurrentGmt()->modify('-90 days');

        $orders = [];
        foreach ($ordersData as $ottoOrderData) {
            try {
                $orderCreateDate = \M2E\Otto\Helper\Date::createDateGmt($ottoOrderData['create_date']);

                if (
                    !$this->isValidOrderByAccountCreateData($accountCreateDate, $boundaryCreationDate, $orderCreateDate)
                ) {
                    continue;
                }

                $orderBuilder = $this->orderBuilderFactory->create();
                $orderBuilder->initialize($account, $ottoOrderData);

                $order = $orderBuilder->process();

                if ($order !== null) {
                    $orders[] = $order;
                }
            } catch (\Throwable $exception) {
                $this->syncLogService->addFromException($exception);
                $this->exceptionHelper->process($exception);

                continue;
            }
        }

        return array_filter($orders);
    }

    /**
     * @param \M2E\Otto\Model\Order[] $orders
     */
    public function processMagentoOrders(array $orders): void
    {
        foreach ($orders as $order) {
            if ($this->isOrderChangedInParallelProcess($order)) {
                continue;
            }

            try {
                $this->createMagentoOrder($order);
            } catch (\Throwable $exception) {
                $this->syncLogService->addFromException($exception);
                $this->exceptionHelper->process($exception);

                continue;
            }
        }
    }

    public function createMagentoOrder(\M2E\Otto\Model\Order $order)
    {
        if ($order->canCreateMagentoOrder()) {
            try {
                $order->getLogService()->setInitiator(\M2E\Otto\Helper\Data::INITIATOR_EXTENSION);

                $order->addInfoLog(
                    'Magento order creation rules are met. M2E Otto will attempt to create Magento order.',
                    [],
                    [],
                    true
                );

                $order->createMagentoOrder();
            } catch (\Throwable $exception) {
                return;
            }
        }

        if ($order->getReserve()->isNotProcessed() && $order->isReservable()) {
            $order->getReserve()->place();
        }

        if ($order->canCreateInvoice()) {
            $order->createInvoice();
        }

        $order->createShipments();

        if ($order->canCreateTracks()) {
            $order->createTracks();
        }
    }

    /**
     * This is going to protect from Magento Orders duplicates.
     * (Is assuming that there may be a parallel process that has already created Magento Order)
     * But this protection is not covering cases when two parallel cron processes are isolated by mysql transactions
     */
    public function isOrderChangedInParallelProcess(\M2E\Otto\Model\Order $order): bool
    {
        $dbOrder = $this->orderRepository->find((int)$order->getId());
        if ($dbOrder === null) {
            return false;
        }

        if ($dbOrder->getMagentoOrderId() !== $order->getMagentoOrderId()) {
            return true;
        }

        return false;
    }

    private function isValidOrderByAccountCreateData(
        \DateTime $accountCreateDate,
        \DateTime $boundaryCreationDate,
        \DateTime $orderCreateDate
    ): bool {
        if (!$this->isValidateAccountCreateDate) {
            return true;
        }

        if ($orderCreateDate >= $accountCreateDate) {
            return true;
        }

        return $orderCreateDate >= $boundaryCreationDate;
    }
}