<?php
/**
 Do you wish to enable Quote on this product
 */
namespace Motus\Quotesystem\Block\Rewrite\Cart;

class Sidebar extends \Magento\Checkout\Block\Cart\Sidebar
{

    /**
     * construct
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Customer\CustomerData\JsLayoutDataProviderPoolInterface $jsLayoutDataProvider
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Customer\CustomerData\JsLayoutDataProviderPoolInterface $jsLayoutDataProvider,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        array $data = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        parent::__construct($context, $customerSession, $checkoutSession, $imageHelper, $jsLayoutDataProvider, $data);
        $this->_isScopePrivate = false;
        $this->imageHelper = $imageHelper;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->customerSessionFactory = $customerSessionFactory;
    }

    /**
     * Quote config data
     *
     * @return array
     */
    public function getQuoteConfig()
    {
        return [
            'removeurl' => $this->getRemoveUrl(),
            'updateItemQtyUrl' => $this->getUpdateUrl(),
            'generateQuoteUrl' => $this->getGenerateQuoteUrl(),
        ];
    }

    /**
     * get remove item url
     *
     * @return string
     */
    public function getRemoveUrl()
    {
        return $this->getUrl('quotesystem/sidebar/removeItem', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * get update item url
     *
     * @return string
     */
    public function getUpdateUrl()
    {
        return $this->getUrl('quotesystem/sidebar/updateItem', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * get generate quote url
     *
     * @return string
     */
    public function getGenerateQuoteUrl()
    {
        return $this->getUrl('quotesystem/sidebar/generateQuote', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * serialize data
     *
     * @return string
     */
    public function getSerializedQuoteConfig()
    {
        return $this->serializer->serialize($this->getQuoteConfig());
    }

    /**
     * Check customer is login or not
     *
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return $this->customerSessionFactory->create()->isLoggedIn();
    }
}
