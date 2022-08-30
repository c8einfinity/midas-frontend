<?php

namespace C8EEE\Parcelninja\Logger;

use Magento\Framework\Logger\Handler\Base;

class Handler extends Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/Parcelninja.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::DEBUG;
}
