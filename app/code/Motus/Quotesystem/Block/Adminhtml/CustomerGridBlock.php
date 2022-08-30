<?php
/**
 * Block for Customer list at admin end.

 */

namespace Motus\Quotesystem\Block\Adminhtml;

class CustomerGridBlock extends \Magento\Framework\View\Element\Template
{

    /**
     * construct
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Backend\Model\Session $backendSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Backend\Model\Session $backendSession,
        array $data = []
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->formKey = $formKey;
        $this->backendSession = $backendSession;
        parent::__construct($context, $data);
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

    /**
     * get form key
     *
     * @return string
     */
    public function getFormKey()
    {
         return $this->formKey->getFormKey();
    }

    /**
     * chec sorting or filter is applied on grid
     *
     * @return bool
     */
    public function isSortData()
    {
        return $this->backendSession->getIsSort();
    }
}
