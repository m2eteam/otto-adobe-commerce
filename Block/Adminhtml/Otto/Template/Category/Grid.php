<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Template\Category;

use M2E\Otto\Model\Category;
use M2E\Otto\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

class Grid extends \M2E\Otto\Block\Adminhtml\Magento\Grid\AbstractGrid
{
    private CategoryCollectionFactory $categoryCollectionFactory;
    private \M2E\Otto\Model\ResourceModel\Product $productResource;
    private \M2E\Core\Ui\AppliedFilters\Manager $appliedFiltersManager;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Product $productResource,
        \M2E\Core\Ui\AppliedFilters\Manager $appliedFiltersManager,
        CategoryCollectionFactory $categoryCollectionFactory,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;

        parent::__construct($context, $backendHelper, $data);
        $this->productResource = $productResource;
        $this->appliedFiltersManager = $appliedFiltersManager;
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('ottoTemplateCategoryGrid');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setDefaultSort('id');
        $this->setDefaultDir('asc');
    }

    protected function _prepareCollection()
    {
        $collection = $this->categoryCollectionFactory->create();

        $collection->getSelect()->where(
            'main_table.state != ?',
            Category::DRAFT_STATE
        );

        $collection->joinLeft(
            ['products' => $this->createProductCountJoinTable()],
            'template_category_id = id',
            ['product_count' => 'count']
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'title',
            [
                'header' => __('Title'),
                'align' => 'left',
                'type' => 'text',
                'escape' => true,
                'index' => 'title'
            ]
        );

        $this->addColumn(
            'product_count',
            [
                'header' => __('Products'),
                'align' => 'center',
                'type' => 'number',
                'index' => 'product_count',
                'filter_index' => 'products.count',
                'frame_callback' => [$this, 'callbackColumnProductCount'],
            ]
        );

        $this->addColumn(
            'total_attributes',
            [
                'header' => __('Attributes: Total'),
                'align' => 'left',
                'type' => 'text',
                'width' => '100px',
                'index' => 'total_product_attributes',
                'filter' => false,
            ]
        );

        $this->addColumn(
            'used_attributes',
            [
                'header' => __('Attributes: Used'),
                'align' => 'left',
                'type' => 'text',
                'width' => '100px',
                'index' => 'used_product_attributes',
                'filter' => false,
            ]
        );

        $this->addColumn(
            'actions',
            [
                'header' => __('Actions'),
                'align' => 'left',
                'width' => '70px',
                'type' => 'action',
                'index' => 'actions',
                'filter' => false,
                'sortable' => false,
                'renderer' => \M2E\Otto\Block\Adminhtml\Magento\Grid\Column\Renderer\Action::class,
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => [
                            'base' => '*/otto_category/view',
                            'params' => [
                                'category_id' => '$id',
                            ],
                        ],
                        'field' => 'id',
                    ],
                ],
            ]
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('main_table.id');
        $this->getMassactionBlock()->setFormFieldName('ids');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Remove'),
                'url' => $this->getUrl('*/otto_category/delete'),
                'confirm' => __('Are you sure?'),
            ]
        );

        return parent::_prepareMassaction();
    }

    public function callbackColumnProductCount($value, $row, $column, $isExport): string
    {
        if (empty($value)) {
            return '0';
        }

        $appliedFiltersBuilder = new \M2E\Core\Ui\AppliedFilters\Builder();
        $appliedFiltersBuilder->addSelectFilter('product_template_category_id', [$row->getId()]);

        $url = $this->appliedFiltersManager->createUrlWithAppliedFilters(
            '*/product_grid/allItems',
            $appliedFiltersBuilder->build()
        );

        return sprintf('<a href="%s" target="_blank">%s</a>', $url, $value);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    public function getRowUrl($item)
    {
        return false;
    }

    private function createProductCountJoinTable(): \Magento\Framework\DB\Select
    {
        return $this->productResource
            ->getConnection()
            ->select()
            ->from(
                ['temp' => $this->productResource->getMainTable()],
                [
                    'template_category_id' => $this->productResource::COLUMN_TEMPLATE_CATEGORY_ID,
                    'count' => new \Zend_Db_Expr('COUNT(*)'),
                ]
            )
            ->group($this->productResource::COLUMN_TEMPLATE_CATEGORY_ID);
    }
}
