<?php
namespace Motus\Tools\Block\Admin;

use Magento\Framework\View\Element\Template;

class Stores extends \Magento\Framework\View\Element\Template
{
    protected $_storeManager;
    protected $_resourceConnection;
    protected $_objectManager;
    protected $_formKey;

    protected $_request;


    /**
     * Stores constructor.
     * @param Template\Context $context
     * @param array $data
     */
    function __construct(Template\Context $context,
                         \Magento\Framework\Data\Form\FormKey $formKey,
                         \Magento\Framework\App\Request\Http $request,

                         array $data = [])
    {
        parent::__construct($context, $data);
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $this->_resourceConnection = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $this->_formKey = $formKey;
        $this->_request = $request;


    }

    /**
     * Gets a form key
     * @return mixed
     */
    function getFormKey() {
        return $this->_formKey->getFormKey();
    }

    /**
     * Gets the http request
     * @return \Magento\Framework\App\Request\Http
     */
    public function getRequest() {
        return $this->_request;
    }
}
