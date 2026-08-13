<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order;

class ShipmentService
{
    /** @var \M2E\Otto\Model\Order\Change\Repository */
    private Change\Repository $changeRepository;
    /** @var \M2E\Otto\Model\Order\Shipment\ItemLoader */
    private Shipment\ItemLoader $itemLoader;
    /** @var \M2E\Otto\Model\Order\Shipment\TrackingPairBuilder */
    private Shipment\TrackingPairBuilder $trackingPairBuilder;

    public function __construct(
        \M2E\Otto\Model\Order\Change\Repository $changeRepository,
        \M2E\Otto\Model\Order\Shipment\ItemLoader $itemLoader,
        \M2E\Otto\Model\Order\Shipment\TrackingPairBuilder $trackingPairBuilder
    ) {
        $this->changeRepository = $changeRepository;
        $this->itemLoader = $itemLoader;
        $this->trackingPairBuilder = $trackingPairBuilder;
    }

    public function shipByShipment(
        \M2E\Otto\Model\Order $order,
        \Magento\Sales\Model\Order\Shipment $shipment,
        int $initiator
    ): void {
        if (!$order->canUpdateShippingStatus()) {
            return;
        }

        $trackingPair = $this->trackingPairBuilder->build($order, $shipment);
        if (!$trackingPair->hasPrimaryDetails()) {
            $this->removeExistChangesForOrderByShipment($order, $shipment);

            return;
        }

        $items = $this->itemLoader->loadItemsByShipment($order, $shipment);
        if (empty($items)) {
            return;
        }

        $this->createOrderChange($order, $items, $trackingPair, $initiator, (int)$shipment->getId());
    }

    private function createOrderChange(
        \M2E\Otto\Model\Order $order,
        array $items,
        \M2E\Otto\Model\Order\Shipment\TrackingPair $trackingPair,
        int $initiator,
        int $shipmentId
    ): void {
        $primaryDetails = $trackingPair->getPrimaryDetails();

        $params = [
            'tracking_number' => $primaryDetails->getTrackingNumber(),
            'shipping_carrier' => $primaryDetails->getCarrierTitle(),
            'shipping_carrier_service_code' => $primaryDetails->getCarrierCode(),
            'ship_date' => \M2E\Core\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s'),
            'items' => [],
        ];

        if ($trackingPair->hasReturnDetails()) {
            $params['return_carrier_code'] = $trackingPair->getReturnDetails()->getCarrierCode();
            $params['return_tracking_number'] = $trackingPair->getReturnDetails()->getTrackingNumber();
        }

        foreach ($items as $item) {
            $params['items'][] = [
                'item_id' => $item->getId(),
            ];
        }

        $this->changeRepository->createShippingOrUpdateNotProcessed(
            $order,
            $params,
            $initiator,
            $shipmentId,
        );
    }

    private function removeExistChangesForOrderByShipment(
        \M2E\Otto\Model\Order $order,
        \Magento\Sales\Model\Order\Shipment $shipment
    ): void {
        $existChanges = $this->changeRepository->findWithActionShippingByOrder(
            $order->getId(),
            (int)$shipment->getId(),
        );

        foreach ($existChanges as $change) {
            $this->changeRepository->delete($change);
        }
    }
}
