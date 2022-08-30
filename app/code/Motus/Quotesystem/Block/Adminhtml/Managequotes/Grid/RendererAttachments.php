<?php
/**
 * @category  Motus
 * @package   Motus_Quotesystem
 * @author    Motus
 */

namespace Motus\Quotesystem\Block\Adminhtml\Managequotes\Grid;

use Motus\Quotesystem\Helper\Data;
use Motus\Quotesystem\Model\Quoteconversation;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

class RendererAttachments extends AbstractRenderer
{
    /**
     * Array to store all options data
     *
     * @var array
     */
    private $_actions = [];

    /**
     * @var Motus\Quotesystem\Helper\Data
     */
    private $_quoteHelper;

    /**
     * @var Quoteconversation
     */
    private $quoteconversation;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param Data                           $quoteHelper
     * @param Quoteconversation              $quoteconversation
     * @param array                          $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        Data $quoteHelper,
        Quoteconversation $quoteconversation,
        \Magento\Framework\Escaper $escaper,
        array $data = []
    ) {
        $this->_quoteHelper = $quoteHelper;
        $this->quoteconversation = $quoteconversation;
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
        $id = $row->getId();
        $conversation = $this->quoteconversation->load($id);
        $conversationAttachments = $this->_quoteHelper->getQuoteAttachmentsArr(
            $conversation->getAttachments()
        );
        if (is_array($conversationAttachments) && count($conversationAttachments)) {
            $actions = $conversationAttachments;
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
        if (count($actions)) {
            foreach ($actions as $attachmentKey => $attachmentVal) {
                $attachmentUrl = $this->_quoteHelper->getMediaUrl().'motquote\files'.$attachmentKey;
                $html[] = '<div>'.
                    '<a href="'.$attachmentUrl.'" target="blank" title="'.$attachmentVal.'">'.$attachmentVal.'</a>'.
                '</div>';
            }
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
        $this->_actions = $actionArray;
    }
}
