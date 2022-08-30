<?php
/**
 * Quote Manage quotes cotroller admin.
 */

namespace Motus\Quotesystem\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class Managequotes extends \Magento\Backend\App\Action
{
    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Motus_Quotesystem::quotes'
        );
    }
}
