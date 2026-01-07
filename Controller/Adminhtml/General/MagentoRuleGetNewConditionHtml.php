<?php

namespace M2E\Otto\Controller\Adminhtml\General;

class MagentoRuleGetNewConditionHtml extends \M2E\Otto\Controller\Adminhtml\AbstractGeneral
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct();
        $this->objectManager = $objectManager;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $prefix = $this->getRequest()->getParam('prefix');
        $storeId = $this->getRequest()->getParam('store', 0);

        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $attributeCode = !empty($typeArr[1]) ? $typeArr[1] : '';
        if (count($typeArr) == 3) {
            $attributeCode = !empty($typeArr[2]) ? $typeArr[2] : '';
        }

        /** @var \M2E\Otto\Model\Magento\Product\Rule\Condition\AbstractModel $model */
        $model = $this->objectManager->create($type);

        $model->setId($id);
        $model->setType($type);
        $model->setRule($this->objectManager->create(\M2E\Otto\Model\Magento\Product\Rule::class));
        $model->setPrefix($prefix);

        if ($type == '\M2E\Otto\Model\Magento\Product\Rule\Condition\Combine') {
            $model->setData($prefix, []);
        }

        if (!empty($attributeCode)) {
            $model->setAttribute($attributeCode);
        }

        if ($model instanceof \Magento\Rule\Model\Condition\ConditionInterface) {
            /** @var \M2E\Otto\Model\Magento\Product\Rule\Condition\Combine $model */
            $model->setJsFormObject($prefix);
            $model->setStoreId($storeId);
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->setAjaxContent($html);

        return $this->getResult();
    }
}
