<?php
/**
 * Column field for sender name in quote conversation grid
 */

namespace Motus\Quotesystem\Block\Adminhtml\Managequotes\Grid;

use Motus\Quotesystem\Helper\Data;
use Motus\Quotesystem\Api\QuoteRepositoryInterface;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

class RendererReceiverTime extends AbstractRenderer
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
     * @var Motus\Quotesystem\Api\QuoteRepositoryInterface
     */
    protected $_quoteRepository;
    protected $_timezone;
    protected $_helper;
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param Data                           $quoteHelper
     * @param QuoteRepositoryInterface       $quoteRepository
     * @param array                          $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        Data $quoteHelper,
        \Motus\Quotesystem\Helper\Data $dataHelper,
        QuoteRepositoryInterface $quoteRepository,
        \Magento\Framework\Escaper $escaper,
        array $data = []
    ) {
        $this->_quoteHelper = $quoteHelper;
        $this->_quoteRepository = $quoteRepository;
        $this->_timezone = $context->getLocaleDate();
        $this->_helper = $dataHelper;
        $this->escaper = $escaper;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $time = $row->getData($this->getColumn()->getIndex());
        return $this->_localeDate->date(
            new \DateTime($this->_localeDate->date(new \DateTime($time))->format('Y-m-d H:i:s'))
        )->format('M j, Y, H:i:s A');
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
