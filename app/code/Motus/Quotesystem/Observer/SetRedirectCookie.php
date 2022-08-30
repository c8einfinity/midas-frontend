<?php
/**
 Do you wish to enable Quote on this product
 */

namespace Motus\Quotesystem\Observer;

use Magento\Framework\Event\ObserverInterface;
use Motus\Quotesystem\Helper\Data;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;

class SetRedirectCookie implements ObserverInterface
{
    protected $redirect;

    protected $accountRedirect;

    protected $checkoutSession;
    
    /**
     * @param AccountRedirect                                   $accountRedirect
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Checkout\Model\Session                   $checkoutSession
     */
    public function __construct(
        AccountRedirect $accountRedirect,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->accountRedirect = $accountRedirect;
        $this->redirect = $redirect;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * quote Item qty Set after
     *
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $refererUrl = $this->checkoutSession->getReferUrl();
        $this->accountRedirect->setRedirectCookie($refererUrl);
    }
}
