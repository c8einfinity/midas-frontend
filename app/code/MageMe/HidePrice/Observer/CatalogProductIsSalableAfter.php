<?php
/**
 * MageMe
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageMe.com license that is
 * available through the world-wide-web at this URL:
 * https://mageme.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    MageMe
 * @package     MageMe_HidePrice
 * @author      MageMe Team <support@mageme.com>
 * @copyright   Copyright (c) MageMe (https://mageme.com)
 * @license     https://mageme.com/license
 */

namespace MageMe\HidePrice\Observer;

use MageMe\HidePrice\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use function var_dump;

/**
 * Class CatalogProductIsSalableAfter
 * @package MageMe\HidePrice\Observer
 */
class CatalogProductIsSalableAfter implements ObserverInterface
{
    /** @var Data */
    private $helper;

    public function __construct(
        Data $helper
    )
    {
        $this->helper = $helper;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $salable = $observer->getSalable();

        // show options on product page
       // if (!$this->helper->isProductView() || $this->helper->hideOptions())
       //     $salable->setData('is_salable', !$this->helper->hidePrice());
    }
}
