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

/**
 * Button "Create Profile" in "New Profile" slide-out panel of a finder page
 */
class CreateProfile implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save'),
            'class' => 'action save primary',
            'id' => 'save-new_profile',
            'on_click' => '',
            'sort_order' => 80,
            'data_attribute' => [
                'mage-init' => [
                    'Magento_Ui/js/form/button-adapter' => [
                        'actions' => [
                            [
                                'targetName' => 'import_profile_edit_form.import_profile_edit_form',
                                'actionName' => 'save',
                                'params' => [
                                    true,
                                    ['popup' => 1, 'finder_tab' => 'variation'],
                                ]
                            ]
                        ]
                    ]
                ],

            ]
        ];
    }
}
