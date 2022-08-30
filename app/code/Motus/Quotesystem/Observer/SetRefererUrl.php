<?php
/**
 Do you wish to enable Quote on this product
 */

namespace Motus\Quotesystem\Observer;

use Magento\Framework\Event\ObserverInterface;

class SetRefererUrl implements ObserverInterface
{
    protected $redirect;

    protected $checkoutSession;
   
    public function __construct(
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
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
        $refererUrl = $this->redirect->getRefererUrl();
        $this->checkoutSession->setReferUrl($refererUrl);
    }
}
