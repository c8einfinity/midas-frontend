<?php
/**
 * Render Receiver name in admin conversation grid

 */

namespace Motus\Quotesystem\Block\Adminhtml\Managequotes\Grid;

use Motus\Quotesystem\Helper\Data;
use Motus\Quotesystem\Api\QuoteRepositoryInterface;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

class RendererReceiverName extends AbstractRenderer
{
    /**
     * Array to store all options data
     *
     * @var array
     */
    protected $_actions = [];
    /**
     * @var Motus\Quotesystem\Helper\Data
     */
    protected $_quoteHelper;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param Data                           $quoteHelper
     * @param array                          $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        Data $quoteHelper,
        \Magento\Framework\Escaper $escaper,
        array $data = []
    ) {
        $this->_quoteHelper = $quoteHelper;
        $this->escaper = $escaper;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $this->_actions = [];
        $actions = [];
        if ($row->getReceiver() == 0) {
            $actions[] = __('Admin');
        } elseif ($row->getReceiver() > 0) {
            $receiverData = $this->_quoteHelper->getCustomerData(
                $row->getReceiver()
            );
            $actions[] = $receiverData->getName();
        }
        $this->addToActions($actions);
        return $this->_actionsToHtml();
    }

    /**
     * Render options array as a HTML string
     *
     * @param  array $actions
     * @return string
     */
    protected function _actionsToHtml(array $actions = [])
    {
        $html = [];
        $attributesObject = new \Magento\Framework\DataObject();

        if (empty($actions)) {
            $actions = $this->_actions;
        }
        foreach ($actions[0] as $action) {
            $html[] = '<span>' . $action . '</span>';
        }
        return implode('', $html);
    }

    /**
     * Add one action array to all options data storage
     *
     * @param  array $actionArray
     * @return void
     */
    public function addToActions($actionArray)
    {
        $this->_actions[] = $actionArray;
    }
}
