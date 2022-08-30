<?php

namespace C8EEE\Parcelninja\Model\Config\Source;

class ConfigOption implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'S1', 'label' => __('Next business day')],
            ['value' => 'S2', 'label' => __('In two business days')],
            ['value' => 'S3', 'label' => __('In three business days')],
            ['value' => 'SZ', 'label' => __('More than three business days')],
            ['value' => 'S0', 'label' => __('Collect from warehouse')]
        ];
    }
}
