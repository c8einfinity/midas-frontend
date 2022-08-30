<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_PartFinder
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\PartFinder\Ui\DataProvider\Profile\Form\Modifier;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

/**
 * Class AbstractModifier
 */
abstract class AbstractModifier implements ModifierInterface
{
    /**
     * Container fieldset prefix
     */
    const CONTAINER_PREFIX = 'container_';
    const FORM_NAME = 'import_profile_edit_form';
    /**
     * Format price to have only two decimals after delimiter
     *
     * @param mixed $value
     * @return string
     * @since 101.0.0
     */
    protected function formatPrice($value)
    {
        return $value !== null ? number_format((float)$value, PriceCurrencyInterface::DEFAULT_PRECISION, '.', '') : '';
    }
}
