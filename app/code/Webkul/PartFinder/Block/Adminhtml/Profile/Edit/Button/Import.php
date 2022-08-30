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
namespace Webkul\PartFinder\Block\Adminhtml\Profile\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Webkul\PartFinder\Block\Adminhtml\Partfinder\Edit\GenericButton;

/**
 * Class Import
 */
class Import implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        $data = [
            'label' => __('Import'),
            'class' => 'action-primary',
            'on_click' => '',
            'data_attribute' => [
                'mage-init' => [
                    'button' => ['event' => 'startImport']
                ],
            ],
            'sort_order' => 20,
        ];

        return $data;
    }
}
