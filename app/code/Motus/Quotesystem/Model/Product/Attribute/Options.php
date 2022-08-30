<?php
/**
 * ImageOption.php
 */

namespace Motus\Quotesystem\Model\Product\Attribute;

class Options extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [
            ['value' => 0, 'label' => __('Select')],
            ['value' => 1, 'label' => __('Yes')],
            ['value' => 2, 'label' => __('No')]
        ];
        return $this->_options;
    }
}
