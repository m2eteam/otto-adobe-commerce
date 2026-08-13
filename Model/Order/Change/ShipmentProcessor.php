<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order\Change;

class ShipmentProcessor
{
    private const MAX_UPDATES_PER_TIME = 50;
    private const ALLOWED_RETURN_CARRIERS = ['DHL', 'GLS', 'HERMES'];

    /** @var \M2E\Otto\Model\Order\Change\Repository */
    private Repository $changeRepository;
    private \M2E\Otto\Model\Order\Shipment\Config $shipmentConfig;
    private \M2E\Otto\Model\Order\Repository $orderRepository;
    private \M2E\Otto\Model\Otto\Connector\Order\Packages\Ship\Processor $connectorProcessor;

    public function __construct(
        \M2E\Otto\Model\Order\Change\Repository $changeRepository,
        \M2E\Otto\Model\Order\Repository $orderRepository,
        \M2E\Otto\Model\Order\Shipment\Config $shipmentConfig,
        \M2E\Otto\Model\Otto\Connector\Order\Packages\Ship\Processor $connectorProcessor
    ) {
        $this->changeRepository = $changeRepository;
        $this->shipmentConfig = $shipmentConfig;
        $this->orderRepository = $orderRepository;
        $this->connectorProcessor = $connectorProcessor;
    }

    public function process(\M2E\Otto\Model\Account $account): void
    {
        $changes = $this->changeRepository->findShippingForProcess($account, self::MAX_UPDATES_PER_TIME);
        if (empty($changes)) {
            return;
        }

        /**
         * @var array<string, array{order: \M2E\Otto\Model\Order, change: \M2E\Otto\Model\Order\Change}> $processedChanges
         */
        $processedChanges = [];

        $shipFromCity = $this->shipmentConfig->getBaseShippingCity();
        $shipFromCountryCode = $this->shipmentConfig->getBaseShippingCountry();
        $shipFromZipCode = $this->shipmentConfig->getBaseShippingZip();

        $requestPackages = [];
        foreach ($changes as $change) {
            $order = $this->orderRepository->find($change->getOrderId());
            if (
                $order === null
                || !$order->canUpdateShippingStatus()
            ) {
                $this->removeChange($change);
                continue;
            }

            $this->changeIncreaseAttempt($change);

            $changeParams = $change->getParams();

            $shipItems = [];
            foreach ($changeParams['items'] as $itemData) {
                $orderItem = $order->findItem((int)$itemData['item_id']);
                if (
                    $orderItem === null
                    || !$orderItem->canUpdateShippingStatus()
                ) {
                    continue;
                }

                if (
                    $this->isDeliveryTypeParcel($orderItem)
                    && !$this->isValidReturnTracking($changeParams)
                ) {
                    $order->addErrorLog(
                        'Return tracking details are not valid. Please provide the return ' .
                        'tracking number for one of the allowed carriers: DHL, GLS, HERMES'
                    );

                    break;
                }

                $shipItems[] = new \M2E\Otto\Model\Otto\Connector\Order\Packages\Ship\PositionItem(
                    $orderItem->getOttoItemId(),
                    $order->getOttoOrderId(),
                    $changeParams['return_carrier_code'] ?? null,
                    $changeParams['return_tracking_number'] ?? null,
                    $orderItem->getDeliveryType()
                );
            }

            if (empty($shipItems)) {
                continue;
            }

            $processedChanges[$order->getOttoOrderId()] = [
                'order' => $order,
                'change' => $change,
            ];

            $requestPackages[] = new \M2E\Otto\Model\Otto\Connector\Order\Packages\Ship\Package(
                $order->getOttoOrderId(),
                $changeParams['shipping_carrier_service_code'],
                $changeParams['tracking_number'],
                $changeParams['ship_date'],
                $shipFromCity,
                $shipFromCountryCode,
                $shipFromZipCode,
                $shipItems,
            );
        }

        if (empty($requestPackages)) {
            $this->removeAllChanges($changes);

            return;
        }

        $response = $this->connectorProcessor->process(
            $account,
            $requestPackages,
        );

        foreach ($response->getErrors() as $error) {
            if (!isset($processedChanges[$error->getPackageId()])) {
                continue;
            }

            $order = $processedChanges[$error->getPackageId()]['order'];
            $message = (string)__(
                'Shipping order error. Reason: %reason',
                ['reason' => $error->getMessage()],
            );
            $order->addErrorLog($message);

            $this->removeChange($processedChanges[$error->getPackageId()]['change']);

            unset($processedChanges[$error->getPackageId()]);
        }

        foreach ($processedChanges as $changeData) {
            $changeParams = $changeData['change']->getParams();

            $carrier = $changeParams['shipping_carrier_service_code'];
            $tracking = $changeParams['tracking_number'];
            $returnCarrier = $changeParams['return_carrier_code'] ?? null;
            $returnTracking = $changeParams['return_tracking_number'] ?? null;

            $params = [
                '!tracking' => $tracking,
                '!carrier' => $carrier,
                '!channel_title' => \M2E\Otto\Helper\Module::getChannelTitle(),
            ];
            $message = 'Order status was updated to Shipped. Tracking number %tracking% for %carrier% has been sent to %channel_title%.';

            if ($returnCarrier && $returnTracking) {
                $message = 'Order status was updated to Shipped. Tracking number %tracking% for %carrier% and return' .
                    ' tracking number %returntracking% for %returncarrier% has been sent to %channel_title%.';
                $params['!returntracking'] = $returnTracking;
                $params['!returncarrier'] = $returnCarrier;
                $params['!channel_title'] = \M2E\Otto\Helper\Module::getChannelTitle();
            }

            $changeData['order']->addSuccessLog($message, $params);

            $this->removeChange($changeData['change']);
        }
    }

    private function changeIncreaseAttempt(\M2E\Otto\Model\Order\Change $change): void
    {
        $change->increaseAttempt();

        $this->changeRepository->save($change);
    }

    private function removeChange(\M2E\Otto\Model\Order\Change $change): void
    {
        $this->changeRepository->delete($change);
    }

    private function removeAllChanges(array $changes): void
    {
        foreach ($changes as $change) {
            $this->removeChange($change);
        }
    }

    private function isDeliveryTypeParcel(\M2E\Otto\Model\Order\Item $orderItem): bool
    {
        return $orderItem->getDeliveryType() === \M2E\Otto\Model\Template\Shipping::DELIVERY_TYPE_PARCEL;
    }

    private function isValidReturnTracking(array $changeParams): bool
    {
        $trackingNumber = $changeParams['tracking_number'] ?? '';
        $returnTrackingNumber = $changeParams['return_tracking_number'] ?? '';
        $returnCarrierCode = $changeParams['return_carrier_code'] ?? '';

        if (empty($returnTrackingNumber)) {
            return false;
        }

        if (!$this->isAllowedReturnCarrier($returnCarrierCode)) {
            return false;
        }

        if ($returnTrackingNumber === $trackingNumber) {
            return false;
        }

        return true;
    }

    private function isAllowedReturnCarrier(string $carrierCode): bool
    {
        foreach (self::ALLOWED_RETURN_CARRIERS as $allowedCarrierCode) {
            if (mb_strtolower($allowedCarrierCode) === mb_strtolower($carrierCode)) {
                return true;
            }
        }

        return false;
    }
}
