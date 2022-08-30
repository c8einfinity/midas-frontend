<?php
/**
 * Add Tab in admin displays all the conversation of that quote.
 */

namespace Motus\Quotesystem\Block\Adminhtml\Managequotes\Edit\Tab;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Extended;
use Motus\Quotesystem\Model\ResourceModel\Quoteconversation\Collection;
use Motus\Quotesystem\Helper\Data;
use \Motus\Quotesystem\Model\QuoteconversationFactory;

class QuoteConversation extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    /**
     * @var \Motus\Mpquotesystem\Model\QuoteconversationFactory
     */
    protected $_conversationFactory;
    /**
     * @var \Quoteconversation\Collection
     */
    protected $_quoteconversationCollection;
    /**
     * @var Motus\Mpquotesystem\Helper\Data
     */
    protected $_quoteHelper;

    /**
     * @param \Template\Context            $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param QuoteconversationFactory     $conversationFactory
     * @param \Magento\Framework\Registry  $coreRegistry
     * @param collection                   $quoteconversationCollection
     * @param Data                         $quoteHelper
     * @param array                        $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        QuoteconversationFactory $conversationFactory,
        \Magento\Framework\Registry $coreRegistry,
        Collection $quoteconversationCollection,
        Data $quoteHelper,
        array $data = []
    ) {
        $this->_conversationFactory = $conversationFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_quoteconversationCollection = $quoteconversationCollection;
        $this->_quoteHelper = $quoteHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('quoteConversation_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $params = $this->getRequest()->getParams();
        $collection = $this->_conversationFactory->create()->getCollection()
            ->addFieldToFilter(
                'quote_id',
                [
                    'eq' => $params['entity_id']
                ]
            )
            ->setOrder(
                'created_at',
                'DESC'
            );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {

        $this->addColumn(
            'sender',
            [
                'header'    => __('Sender'),
                'sortable'  => true,
                'index'     => 'sender',
                'filter'    =>  false,
                'renderer'  => \Motus\Quotesystem\Block\Adminhtml\Managequotes\Grid\RendererSenderName ::class,
            ]
        );
        $this->addColumn(
            'receiver',
            [
                'header'    => __('Receiver'),
                'sortable'  => true,
                'index'     => 'receiver',
                'filter'    =>  false,
                'renderer'  => \Motus\Quotesystem\Block\Adminhtml\Managequotes\Grid\RendererReceiverName ::class,
            ]
        );
        $this->addColumn(
            'conversation',
            [
                'header'    => __('Conversation'),
                'sortable'  => true,
                'index'     => 'conversation',
                'type'      => 'text',
            ]
        );
        $this->addColumn(
            'attachments',
            [
                'header'    => __('Attachments'),
                'sortable'  => true,
                'index'     => 'attachments',
                'renderer'  => \Motus\Quotesystem\Block\Adminhtml\Managequotes\Grid\RendererAttachments ::class,
            ]
        );
        $this->addColumn(
            'created_at',
            [
                'header'    => __('Created At'),
                'sortable'  => true,
                'index'     => 'created_at',
                'type'      => 'datetime',
                'renderer'  => \Motus\Quotesystem\Block\Adminhtml\Managequotes\Grid\RendererReceiverTime ::class,
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getRowUrl($row)
    {
        return 'javascript:void(0)';
    }
}
