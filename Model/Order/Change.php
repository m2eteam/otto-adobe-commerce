<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order;

use M2E\Otto\Model\ResourceModel\Order\Change as ChangeResource;

class Change extends \M2E\Otto\Model\ActiveRecord\AbstractModel
{
    public const ACTION_UPDATE_SHIPPING = 'update_shipping';
    public const ACTION_CANCEL = 'cancel';

    public const PARAMS_KEY_ORDER_ITEMS = 'order_items';

    public const MAX_ALLOWED_PROCESSING_ATTEMPTS = 3;

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(ChangeResource::class);
    }

    public function init(
        int $orderId,
        ?int $magentoShipmentId,
        string $action,
        $creatorType,
        array $params,
        string $hash
    ): void {
        if (!in_array($action, self::getAllowedActions())) {
            throw new \InvalidArgumentException('Action is invalid.');
        }

        $this->setData(ChangeResource::COLUMN_ORDER_ID, $orderId)
             ->setData(ChangeResource::COLUMN_MAGENTO_SHIPMENT_ID, $magentoShipmentId)
             ->setData(ChangeResource::COLUMN_ACTION, $action)
             ->setData(ChangeResource::COLUMN_CREATOR_TYPE, $creatorType)
             ->setData(ChangeResource::COLUMN_HASH, $hash)
             ->setData(ChangeResource::COLUMN_PROCESSING_ATTEMPT_COUNT, 0)
             ->setParams($params);
    }

    // ----------------------------------------

    public function getOrderId(): int
    {
        return (int)$this->getData(ChangeResource::COLUMN_ORDER_ID);
    }

    public function getMagentoShipmentId(): int
    {
        return (int)$this->getData(ChangeResource::COLUMN_MAGENTO_SHIPMENT_ID);
    }

    public function isShippingUpdateAction(): bool
    {
        return $this->getAction() === self::ACTION_UPDATE_SHIPPING;
    }

    public function isCancelAction(): bool
    {
        return $this->getAction() === self::ACTION_CANCEL;
    }

    public function getAction(): string
    {
        return (string)$this->getData(ChangeResource::COLUMN_ACTION);
    }

    public function getCreatorType(): int
    {
        return (int)$this->getData(ChangeResource::COLUMN_CREATOR_TYPE);
    }

    public function setParams(array $params): self
    {
        $this->setData(ChangeResource::COLUMN_PARAMS, json_encode($params));

        return $this;
    }

    public function getParams(): array
    {
        $params = $this->getData(ChangeResource::COLUMN_PARAMS);
        if (empty($params)) {
            return [];
        }

        return json_decode($params, true);
    }

    public function getHash(): string
    {
        return (string)$this->getData(ChangeResource::COLUMN_HASH);
    }

    public function getProcessingAttemptCount(): int
    {
        return (int)$this->getData(ChangeResource::COLUMN_PROCESSING_ATTEMPT_COUNT);
    }

    public function increaseAttempt(): self
    {
        $this
            ->setData(
                ChangeResource::COLUMN_PROCESSING_ATTEMPT_COUNT,
                $this->getProcessingAttemptCount() + 1,
            )
            ->setData(
                ChangeResource::COLUMN_PROCESSING_ATTEMPT_DATE,
                \M2E\Core\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s'),
            );

        return $this;
    }

    private static function getAllowedActions(): array
    {
        return [
            self::ACTION_UPDATE_SHIPPING,
            self::ACTION_CANCEL,
        ];
    }
}
