<?php

declare(strict_types=1);

namespace M2E\Otto\Model;

use M2E\Otto\Model\ResourceModel\AdvancedFilter as AdvancedFilterResource;

class AdvancedFilter extends \M2E\Otto\Model\ActiveRecord\AbstractModel
{
    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init(AdvancedFilterResource::class);
    }

    public function getModelNick(): string
    {
        return $this->getDataByKey(AdvancedFilterResource::COLUMN_MODEL_NICK);
    }

    public function setModelNick(string $modelNick): void
    {
        $this->setData(AdvancedFilterResource::COLUMN_MODEL_NICK, $modelNick);
    }

    public function getTitle(): string
    {
        return $this->getDataByKey(AdvancedFilterResource::COLUMN_TITLE);
    }

    public function setTitle(string $title): void
    {
        $this->setData(AdvancedFilterResource::COLUMN_TITLE, $title);
    }

    public function getConditionals(): string
    {
        return $this->getDataByKey(AdvancedFilterResource::COLUMN_CONDITIONALS);
    }

    public function setConditionals(string $conditionals): void
    {
        $this->setData(AdvancedFilterResource::COLUMN_CONDITIONALS, $conditionals);
    }

    public function getUpdateDate(): \DateTime
    {
        return \M2E\Core\Helper\Date::createDateGmt(
            $this->getData(AdvancedFilterResource::COLUMN_UPDATE_DATE)
        );
    }

    /**
     * @throws \DateInvalidTimeZoneException
     */
    public function setUpdateDate(\DateTime $createDate): void
    {
        $timeZone = new \DateTimeZone(\M2E\Core\Helper\Date::getTimezone()->getDefaultTimezone());
        $createDate->setTimezone($timeZone);
        $this->setData(AdvancedFilterResource::COLUMN_UPDATE_DATE, $createDate->format('Y-m-d H:i:s'));
    }

    public function getCreateDate(): \DateTime
    {
        return \M2E\Core\Helper\Date::createDateGmt(
            $this->getData(AdvancedFilterResource::COLUMN_CREATE_DATE)
        );
    }

    /**
     * @throws \DateInvalidTimeZoneException
     */
    public function setCreateDate(\DateTime $createDate): void
    {
        $timeZone = new \DateTimeZone(\M2E\Core\Helper\Date::getTimezone()->getDefaultTimezone());
        $createDate->setTimezone($timeZone);
        $this->setData(AdvancedFilterResource::COLUMN_CREATE_DATE, $createDate->format('Y-m-d H:i:s'));
    }
}
