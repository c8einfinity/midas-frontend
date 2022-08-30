<?php
/**
 Do you wish to enable Quote on this product.
 */

namespace Motus\Quotesystem\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class CmsBlock extends Template
{
    /**
     * Motus\Quotesystem\Helper\Data
     *
     * @var Data
     */
    protected $helper;

    /**
     * @param Context $context
     * @param Data $pixelHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Motus\Quotesystem\Helper\Data $helper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }

    /**
     * get Helper
     *
     * @return Motus\Quotesystem\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * Get Json Helper
     *
     * @return Magento\Framework\Json\Helper\Data
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }
}
