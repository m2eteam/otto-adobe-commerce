<?php

namespace M2E\Otto\Block\Adminhtml\Order\View\Log;

use M2E\Otto\Block\Adminhtml\Log\AbstractGrid;

class Grid extends AbstractGrid
{
    private \M2E\Otto\Model\Order $order;
    private \M2E\Otto\Model\ResourceModel\Order\Log\CollectionFactory $orderLogCollectionFactory;

    public function __construct(
        \M2E\Otto\Model\Order $order,
        \M2E\Otto\Model\ResourceModel\Order\Log\CollectionFactory $orderLogCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \M2E\Otto\Helper\View $viewHelper,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        parent::__construct($resourceConnection, $viewHelper, $context, $backendHelper, $data);
        $this->orderLogCollectionFactory = $orderLogCollectionFactory;
        $this->order = $order;
    }

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('orderViewLogGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('create_date');
        $this->setDefaultDir('DESC');
        $this->setFilterVisibility(false);
        $this->setUseAjax(true);
        $this->setCustomPageSize(false);
        // ---------------------------------------
    }

    protected function _prepareCollection()
    {
        $collection = $this->orderLogCollectionFactory->create();
        $collection->addFieldToFilter('order_id', $this->order->getId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('create_date', [
            'header' => __('Create Date'),
            'align' => 'left',
            'width' => '165px',
            'type' => 'datetime',
            'filter' => \M2E\Otto\Block\Adminhtml\Magento\Grid\Column\Filter\Datetime::class,
            'format' => \IntlDateFormatter::MEDIUM,
            'filter_time' => true,
            'index' => 'create_date',
        ]);

        $this->addColumn('message', [
            'header' => __('Message'),
            'align' => 'left',
            'width' => '*',
            'type' => 'text',
            'sortable' => false,
            'filter_index' => 'id',
            'index' => 'description',
            'frame_callback' => [$this, 'callbackColumnDescription'],
        ]);

        $this->addColumn('initiator', [
            'header' => __('Run Mode'),
            'align' => 'right',
            'width' => '65px',
            'index' => 'initiator',
            'sortable' => false,
            'type' => 'options',
            'options' => $this->_getLogInitiatorList(),
            'frame_callback' => [$this, 'callbackColumnInitiator'],
        ]);

        $this->addColumn('type', [
            'header' => __('Type'),
            'align' => 'right',
            'width' => '65px',
            'index' => 'type',
            'sortable' => false,
            'type' => 'options',
            'options' => $this->_getLogTypeList(),
            'frame_callback' => [$this, 'callbackColumnType'],
        ]);

        return parent::_prepareColumns();
    }

    public function getRowUrl($item)
    {
        return '';
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/order/viewLogGrid', ['_current' => true]);
    }
}
