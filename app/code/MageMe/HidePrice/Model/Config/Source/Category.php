<?php
namespace MageMe\HidePrice\Model\Config;

use Magento\Framework\Option\ArrayInterface;

class CodeCats implements ArrayInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => 'cat',
                'value' => 0
            ],
            [
                'label' => 'cat 1',
                'value' => 1
            ]
        ];
    }
}
