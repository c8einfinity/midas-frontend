<?php
/**
 * Block for Quote list at admin end.

 */

namespace Motus\Quotesystem\Block\Adminhtml\Product\Composite\Fieldset;

use \Magento\ConfigurableProduct\Block\Adminhtml\Product\Composite\Fieldset\Configurable as Conf;

class Configurable extends Conf
{
    
    /**
     * Get Helper
     *
     * @return\Magento\Catalog\Helper\Product
     */
    public function getHelper()
    {
        return $this->catalogProduct;
    }

    /**
     * Get Json Helper
     *
     * @return \Magento\Framework\Json\EncoderInterface
     */
    public function getJsonHelper()
    {
        return $this->jsonEncoder;
    }
}
