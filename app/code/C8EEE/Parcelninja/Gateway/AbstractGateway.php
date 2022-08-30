<?php

namespace C8EEE\Parcelninja\Gateway;

use \Magento\Framework\DataObject;
use \C8EEE\Parcelninja\Logger\Logger;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \C8EEE\Parcelninja\Model\GatewayInterface;
use \Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Store\Model\StoreManager;

abstract class AbstractGateway extends DataObject implements GatewayInterface
{

    protected $_name = '';


    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @var \C8EEE\Sms\Logger\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     *
     */
    protected $scopeConfig;

    /**
     * @var \C8EEE\Sms\Helper\Data
     */
    protected $_helper;


}
