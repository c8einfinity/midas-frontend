<?php
/**
 * ImageOption.php
 */

namespace Motus\Quotesystem\Model\Config\Source;

class Option implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Enable')],
            ['value' => 0, 'label' => __('Disable')]
        ];
    }
}
