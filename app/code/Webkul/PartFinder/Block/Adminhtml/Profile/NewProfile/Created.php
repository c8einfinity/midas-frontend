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
namespace Webkul\PartFinder\Block\Adminhtml\Profile\NewProfile;

use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Json\EncoderInterface;
use Webkul\PartFinder\Model\ProfileDataFactory;

class Created extends Widget
{
    /**
     * @var string
     */
    protected $_template = 'profile/new/created.phtml';

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Webkul\PartFinder\Model\ProfileDataFactory
     */
    protected $profileDataFactory;

    /**
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param ProfileDataFactory $profileDataFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        ProfileDataFactory $profileDataFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->jsonEncoder = $jsonEncoder;
        $this->profileDataFactory = $profileDataFactory;
    }

    /**
     * Retrieve HTML for 'Close' button
     *
     * @return string
     */
    public function getCloseButtonHtml()
    {
        return $this->getChildHtml('close_button');
    }

    /**
     * Retrieve attributes data as JSON
     *
     * @return string
     */
    public function getProfileBlockJson()
    {
        $result = [];
        if ($this->getRequest()->getParam('finder_tab') == 'variation') {
            $profile = $this->profileDataFactory->create()->load(
                $this->getRequest()->getParam('profile')
            );
            $result = [
                'tab' => $this->getRequest()->getParam('finder_tab'),
                'profile' => [
                    'id' => $profile->getId(),
                    'label' => $profile->getName(),
                    'dropdowns' => $profile->getMappingData(),
                ],
            ];
        }
        return $this->jsonEncoder->encode($result);
    }
}
