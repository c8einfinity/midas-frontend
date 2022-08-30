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

namespace MageMe\HidePrice\Plugin;

use MageMe\HidePrice\Helper\Data;
use Magento\Catalog\Pricing\Render\PriceBox as PriceBoxRenderer;
use function strstr;

/**
 * Class PriceBox
 */
class PriceBox
{
    /** @var Data */
    private $hidePriceHelper;

    /**
     * FinalPriceBox constructor.
     * @param Data $hidePriceHelper
     */
    public function __construct(
        Data $hidePriceHelper
    )
    {
        $this->hidePriceHelper = $hidePriceHelper;
    }

    /**
     * @param PriceBoxRenderer $finalPriceBox
     * @param $result
     * @return string
     */
    public function afterToHtml(PriceBoxRenderer $finalPriceBox, $result)
    {
        if (
            strstr($finalPriceBox->getTemplate(), 'tier_prices.phtml')
        ) {
            $isHidePriceEnabled = $this->hidePriceHelper->hidePrice();
            if($isHidePriceEnabled)
                return '';
        }

        return $result;
    }
}
