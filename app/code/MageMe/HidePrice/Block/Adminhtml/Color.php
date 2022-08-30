<?php

namespace MageMe\HidePrice\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Color extends Field
{

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context, array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        $html  = $element->getElementHtml();
        $value = $element->getData('value');

        $html .= '<script type="text/javascript">
            require(["jquery","MageMe_HidePrice/js/colpick"], function ($) {
                $(document).ready(function (e) {
                    var el = $("#' . $element->getHtmlId() . '");
                    el.css("background-color","#' . $value . '");
                    el.colpick({
                        layout:"hex",
                        submit:0,
                        colorScheme:"dark",
                        color: "#' . $value . '",
                        onChange:function(hsb,hex,rgb,el,bySetColor) {
                        $(el).css("background-color","#"+hex);
                        if(!bySetColor) $(el).val(hex);
                    }
                    }).keyup(function(){
                        $(this).colpickSetColor(this.value);
                    });
                });
            });
            </script>';

        return $html;
    }

}
