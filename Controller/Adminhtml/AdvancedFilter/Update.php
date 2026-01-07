<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\AdvancedFilter;

class Update extends \M2E\Otto\Controller\Adminhtml\AbstractBase
{
    private \M2E\Otto\Model\AdvancedFilter\Manager $ruleManager;
    private \M2E\Otto\Model\AdvancedFilter\Repository $repository;

    public function __construct(
        \M2E\Otto\Model\AdvancedFilter\Manager $ruleManager,
        \M2E\Otto\Model\AdvancedFilter\Repository $repository,
        \M2E\Otto\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->ruleManager = $ruleManager;
        $this->repository = $repository;
    }

    public function execute()
    {
        $request = $this->getRequest();
        parse_str($request->getPostValue('form_data'), $formData);
        $formData = $formData['rule'][$request->getPostValue('prefix')] ?? null;
        $title = $request->getPostValue('title');
        $ruleEntityId = $request->getPostValue('rule_entity_id');

        if (empty($ruleEntityId) || $formData === null) {
            throw new \Exception('Invalid input');
        }

        if (empty($title)) {
            $this->setJsonContent(
                ['result' => false, 'message' => __('Please enter a title to save the filter')]
            );

            return $this->getResult();
        }

        $advancedFilter = $this->repository->getAdvancedFilter((int)$ruleEntityId);
        $rule = $this->ruleManager->getRuleModelByNick($advancedFilter->getModelNick());
        if (!$this->ruleManager->isConditionsValid($formData, $rule)) {
            $this->setJsonContent(
                ['result' => false, 'message' => __('Please specify filter conditions before saving it')]
            );
            return $this->getResult();
        }

        $this->ruleManager->update($advancedFilter, $title, $formData);
        $this->setJsonContent(['result' => true]);

        return $this->getResult();
    }
}
