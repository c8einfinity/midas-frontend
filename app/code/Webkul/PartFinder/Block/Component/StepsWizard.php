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
namespace Webkul\PartFinder\Block\Component;

use Magento\Framework\Serialize\Serializer\Json as JsonData;

/**
 * Seller Product's Collection Block.
 */
class StepsWizard extends \Magento\Ui\Block\Component\StepsWizard
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        JsonData $jsonData,
        $data = []
    ) {
        $this->jsonData = $jsonData;
        parent::__construct($context, $data);
    }
    /**
     * Wizard step template
     *
     * @var string
     */
    protected $_template = 'Webkul_PartFinder::stepswizard.phtml';

    /**
     *  Encode the array to json
     *
     * @params array @array
     * @return array
     */
    public function jsonEncode($array)
    {
        return $this->jsonData->serialize($array);
    }
}
