<?php

namespace M2E\Otto\Model\Channel\Magento\Product\Rule\Condition;

class Product extends \M2E\Otto\Model\Magento\Product\Rule\Condition\Product
{
    /**
     * @param mixed $validatedValue
     *
     * @return bool
     */
    public function validateAttribute($validatedValue)
    {
        if (is_array($validatedValue)) {
            $result = false;

            foreach ($validatedValue as $value) {
                $result = parent::validateAttribute($value);
                if ($result) {
                    break;
                }
            }

            return $result;
        }

        return parent::validateAttribute($validatedValue);
    }
}
