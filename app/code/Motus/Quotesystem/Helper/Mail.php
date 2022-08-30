<?php
/**
 * Quote Helper Mail.php
 */

namespace Motus\Quotesystem\Helper;

class Mail extends \Magento\Framework\App\Helper\AbstractHelper
{
    use \Motus\Tools\Block\MotusLocationTrait;

    const XML_PATH_EMAIL_NEW_QUOTE = 'quotesystem/email/new_quote';
    const XML_PATH_EMAIL_QUOTE_STATUS = 'quotesystem/email/quote_status';
    const XML_PATH_EMAIL_QUOTE_MESSAGE = 'quotesystem/email/quote_message';
    const XML_PATH_EMAIL_QUOTE_EDIT = 'quotesystem/email/quote_edit';

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlineTranslation;

    /**
     * @var \Motus\Quotesystem\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var templateId
     */
    protected $_tempId;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Motus\Quotesystem\Model\QuotesFactory
     */
    protected $_quoteFactory;

    /**
     * @var UrlBuilder
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $_backendUrl;

    /**
     * @param \Magento\Framework\Translate\Inline\StateInterface $_inlineTranslation
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Motus\Quotesystem\Helper\Data $helper
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Motus\Quotesystem\Model\QuotesFactory $quoteFactory
     * @param \Magento\Backend\Model\Url $backendUrl
     */
    public function __construct(
        \Magento\Framework\Translate\Inline\StateInterface $_inlineTranslation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Motus\Quotesystem\Helper\Data $helper,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Motus\Quotesystem\Model\QuotesFactory $quoteFactory,
        \Magento\Backend\Model\Url $backendUrl
    )
    {
        parent::__construct($context);
        $this->_inlineTranslation = $_inlineTranslation;
        $this->_storeManager = $storeManager;
        $this->_helper = $helper;
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_transportBuilder = $transportBuilder;
        $this->_quoteFactory = $quoteFactory;
        $this->_messageManager = $messageManager;
        $this->_backendUrl = $backendUrl;
    }

    public function getClosestStore($distance = 100000)
    {
        $this->initTina4();

        $ipInfo = $this->getIpInfo();

        $location = $this->getLocation($ipInfo["city"] . ", " . $ipInfo["country"]);
        $points = $location->results[0]->geometry->location;

        $stores = $this->_DBA->fetch("select id,email, name, round(st_distance_sphere( point({$points->lat}, {$points->lng}), point(ms.latitude, ms.longitude)) / 1000 , 2) as km
                                           from motus_store ms
                                           where is_active = 1 and (st_distance_sphere( point({$points->lat}, {$points->lng}), point(ms.latitude, ms.longitude)) / 1000 < {$distance})
                                           order by 4 ", 1)->asArray();

        //error_log('test  ' . print_r($ipInfo,1) . print_r($stores,1));
        return $stores[0];

    }

    public function getStoreFromId($storeId)
    {
        $this->initTina4();
        return $this->_DBA->fetch("select * from from motus_store ms where id = {$storeId}")->asArray()[0];
    }


    // send Mail to customer and admin when new quote is processed
    public function newQuote($quoteId)
    {
        $quote = $this->_quoteFactory->create()->load($quoteId);
        $quoteprice = number_format($quote->getQuotePrice(), 2);
        // admin details
        $admininfo = [];
        $admininfo = [
            'name' => 'admin',
            'email' => $this->_helper->getDefaultTransEmailId(),
        ];
        //store details
        $storeClosest = $this->getClosestStore();

        $quote->setData("motus_store_id", $storeClosest["id"]);
        $quote->setData("motus_store_name", $storeClosest["name"]);

        $quote->save();

        $storeInfo = [];
        $storeClosest['email'] .= "/";
        $storeInfo = [
            'name' => $storeClosest['name'],
            'email' => substr($storeClosest['email'], 0, strpos($storeClosest['email'], "/")), //$storeClose['email'],
        ];
        //customer details
        $customer = $this->_helper->getCustomerData($quote->getCustomerId());
        $customerinfo = [];
        $customerinfo = [
            'name' => $customer->getName(),
            'email' => $customer->getEmail(),
        ];

        $product = $this->_helper->getProduct($quote->getProductId());
        // Product Options  "<dl class='item-options'>".$optionPriceArr[0]."</dl>".
        $optionAndPrice = $this->_helper->getOptionNPrice($product, $quote);
        $optionPriceArr = explode('~|~', $optionAndPrice);
        $productName = '<tbody><tr>' .
            "<td class='item-info'>" . $quote->getProductName() .
            "</td><td class='item-info'>" . $product->getSku() .
            '</td></tr></tbody>';
        // Email variables
        $templateVariable = [];
        $templateVariable['quote_id'] = $quoteId;
        $templateVariable['product_name'] = $productName;
        $templateVariable['quote_qty'] = $quote->getQuoteQty();
        //$templateVariable['quote_price'] = $this->_helper->getCurrentCurrencySymbol().$quoteprice;
        $templateVariable['quote_description'] = $quote->getQuoteDesc();
        $templateVariable['receiver_name'] = $customerinfo['name'];
        $templateVariable['title'] = __('Thanks for your quote, The store will contact you soon.');
        $senderInfo = [];
        $senderInfo = $admininfo;
        // mail template
        // Send mail to customer
        $this->_tempId = $this->getTemplateId(self::XML_PATH_EMAIL_NEW_QUOTE);
        $this->_inlineTranslation->suspend();
        $this->generateTemplate(
            $templateVariable,
            $senderInfo,
            $customerinfo
        );
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
            $this->_inlineTranslation->resume();

            // send Mail to admin
            $templateVariable['receiver_name'] = $admininfo['name'];
            $templateVariable['title'] = __('New Quote has been created.');
            $this->generateTemplate(
                $templateVariable,
                $customerinfo,
                $admininfo
            );
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();

            //Send Mail to Closest Store
            $templateVariable['receiver_name'] = $storeInfo['name'];
            $templateVariable['title'] = __('New Quote has been created.');
            $this->generateTemplate(
                $templateVariable,
                $customerinfo,
                $storeInfo

            );
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    //send mail when quote status gets updated
    public function quoteStatusMail($quoteId, $message, $product)
    {
        $quote = $this->_quoteFactory->create()->load($quoteId);
        $quoteUrl = $this->_urlBuilder->getUrl(
            'quotesystem/buyerquote/edit/',
            ['id' => $quoteId, '_nosid' => 1]
        );
        // admin details
        $admininfo = [];
        $admininfo = [
            'name' => 'Admin',
            'email' => $this->_helper->getDefaultTransEmailId(),
        ];
        //store details
        $storeClose = $this->getClosestStore();
        $storeInfo = [];
        $storeInfo = [
            'name' => $storeClose['name'],
            'email' => substr($storeClose['email'], 0, strpos($storeClose['email'], "/")), //$storeClose['email'],
        ];

        $customer = $this->_helper->getCustomerData($quote->getCustomerId());
        $customerinfo = [];
        $customerinfo = [
            'name' => $customer->getName(),
            'email' => $customer->getEmail(),
        ];
        //status of Quote
        $status = '';
        if ($quote->getStatus() == \Motus\Quotesystem\Model\Quotes::STATUS_UNAPPROVED) {
            $status = 'UnApproved';
        }
        if ($quote->getStatus() == \Motus\Quotesystem\Model\Quotes::STATUS_APPROVED) {
            $status = 'Approved';
        }
        if ($quote->getStatus() == \Motus\Quotesystem\Model\Quotes::STATUS_DECLINE) {
            $status = 'Declined';
        }

        $templateVariable['new_status'] = $status;
        $templateVariable['new_message'] = $message;
        $this->_tempId = $this->getTemplateId(self::XML_PATH_EMAIL_QUOTE_STATUS);
        $this->_inlineTranslation->suspend();
        $proName = $product->getname();
        $prourl = $product->getProductUrl();
        $templateVariable['receiver_name'] = $customerinfo['name'];
        $templateVariable['title'] = __("Status for <a href='%1'>%2</a> has been changed now.", $prourl, $proName);
        $templateVariable['url'] = $quoteUrl;
        // mail template

        // Send mail to customer
        $this->generateTemplate(
            $templateVariable,
            $admininfo,
            $customerinfo
        );
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();

            $this->_inlineTranslation->resume();

            $templateVariable['receiver_name'] = $admininfo['name'];
            $templateVariable['title'] = __('Status changed successfully.');
            unset($templateVariable['url']);
            $this->generateTemplate(
                $templateVariable,
                $customerinfo,
                $admininfo
            );

            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();

            //Send Mail to Closest Store
            $templateVariable['receiver_name'] = $storeInfo['name'];
            $templateVariable['title'] = __('Status changed successfully.');
            $this->generateTemplate(
                $templateVariable,
                $customerinfo,
                $storeInfo

            );
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    //send mail when a new message is added to quote
    public function quoteMessage($quoteId, $message, $flag, $product)
    {
        $quote = $this->_quoteFactory->create()->load($quoteId);

        // admin details
        $admininfo = [];
        $admininfo = [
            'name' => 'Admin',
            'email' => $this->_helper->getDefaultTransEmailId(),
        ];
        //store details
        $storeClosest = $this->getStoreFromId($quote->getData('motus_store_id'));
        $storeClosest['email'] .= "/";
        $storeInfo = [];
        $storeInfo = [
            'name' => $storeClosest['name'],
            'email' => substr($storeClosest['email'], 0, strpos($storeClosest['email'], "/")), //$storeClose['email'],
        ];

        $customer = $this->_helper->getCustomerData($quote->getCustomerId());
        $customerinfo = [];
        $customerinfo = [
            'name' => $customer->getName(),
            'email' => $customer->getEmail(),
        ];

        $this->_tempId = $this->getTemplateId(self::XML_PATH_EMAIL_QUOTE_MESSAGE);
        $this->_inlineTranslation->suspend();

        $templateVariable = [];
        $templateVariable['new_message'] = $message;
        $proName = $product->getName();
        $prourl = $product->getProductUrl();
        $quoteUrl = $this->_urlBuilder->getUrl(
            'quotesystem/buyerquote/edit/',
            ['id' => $quoteId, '_nosid' => 1]
        );

        if ($flag == 'customer') {
            $templateVariable['receiver_name'] = $customerinfo['name'];
            $templateVariable['title'] = __('Your Message sent successfully.');
            $this->generateTemplate(
                $templateVariable,
                $admininfo,
                $customerinfo
            );
            try {
                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();

                $this->_inlineTranslation->resume();

                //Mail To Admin
                //
                $templateVariable['receiver_name'] = $admininfo['name'];
                $templateVariable['title'] = __(
                    "A New Message for quoted product: <a href='%1'>%2</a>",
                    $prourl,
                    $proName
                );
                $this->generateTemplate(
                    $templateVariable,
                    $customerinfo,
                    $admininfo
                );

                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();

                //Send Mail to Closest Store
                $templateVariable['receiver_name'] = $storeInfo['name'];
                $templateVariable['title'] = __(
                    "A New Message for quoted product: <a href='%1'>%2</a>",
                    $prourl,
                    $proName
                );
                $this->generateTemplate(
                    $templateVariable,
                    $customerinfo,
                    $storeInfo

                );
                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();

            } catch (\Exception $e) {
                $this->_messageManager->addError($e->getMessage());
            }
            $this->_inlineTranslation->resume();
        } elseif ($flag == 'admin') {

            //Mail To Customer
            $templateVariable['receiver_name'] = $customerinfo['name'];
            $templateVariable['title'] = __(
                "A New Message for quoted product: <a href='%1'>%2</a><br/>Quote Link:  <a href='%3'>Click</a>",
                $prourl,
                $proName,
                $quoteUrl
            );
            $this->generateTemplate(
                $templateVariable,
                $admininfo,
                $customerinfo
            );
            try {
                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();

                $this->_inlineTranslation->resume();

                //Mail To Admin
                $templateVariable['receiver_name'] = $admininfo['name'];
                $templateVariable['title'] = __('Your Message sent successfully.');
                $this->generateTemplate(
                    $templateVariable,
                    $customerinfo,
                    $admininfo
                );

                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();

                //Send Mail to Closest Store
                $templateVariable['receiver_name'] = $storeInfo['name'];
                $templateVariable['title'] = __('Admin Message sent successfully.');
                $this->generateTemplate(
                    $templateVariable,
                    $customerinfo,
                    $storeInfo

                );
                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();
            } catch (\Exception $e) {
                $this->_messageManager->addError($e->getMessage());
            }
            $this->_inlineTranslation->resume();
        }
    }

    //Quote edited by buyer

    public function quoteEdited($quoteId, $message)
    {
        $quote = $this->_quoteFactory->create()->load($quoteId);
        $quoteprice = number_format($quote->getQuotePrice(), 2);
        // admin details

        $admininfo = [
            'name' => 'Admin',
            'email' => $this->_helper->getDefaultTransEmailId(),
        ];
        //store details
        $storeClosest = $this->getStoreFromId($quote->getData('motus_store_id'));
        $storeClosest['email'] .= "/";
        $storeInfo = [
            'name' => $storeClosest['name'],
            'email' => substr($storeClosest['email'], 0, strpos($storeClosest['email'], "/")), //$storeClose['email'],
        ];

        $customer = $this->_helper->getCustomerData($quote->getCustomerId());

        $customerinfo = [
            'name' => $customer->getName(),
            'email' => $customer->getEmail(),
        ];

        $this->_tempId = $this->getTemplateId(self::XML_PATH_EMAIL_QUOTE_EDIT);
        $this->_inlineTranslation->suspend();

        $templateVariable = [];
        $product = $this->_helper->getProduct($quote->getProductId());
        // Product Options   "<dl class='item-options'>".$optionPriceArr[0]."</dl>".
        $optionAndPrice = $this->_helper->getOptionNPrice($product, $quote);

        $optionPriceArr = explode('~|~', $optionAndPrice);
        $productName = '<tbody><tr>' .
            "<td class='item-info'>" . $quote->getProductName() .
            "</td><td class='item-info'>" . $product->getSku() .
            '</td></tr></tbody>';

        $templateVariable["quote_id"] = $quoteId;
        $templateVariable["product_name"] = $productName;
        $templateVariable["new_quote_qty"] = $quote->getQuoteQty();
        //$templateVariable["new_quote_price"] = $this->_helper->getCurrentCurrencySymbol().$quoteprice;
        $templateVariable["new_message"] = $message;
        $templateVariable["new_options"] = $optionPriceArr[0];
        $templateVariable["edit_by"] = 'customer';

        //Mail To Customer
        $templateVariable["receiver_name"] = $customerinfo['name'];
        $templateVariable["title"] = __("You just Edited your previous quote, details are below.");
        $this->generateTemplate(
            $templateVariable,
            $admininfo,
            $customerinfo
        );
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();

            $this->_inlineTranslation->resume();

            //Mail To Admin
            $templateVariable["receiver_name"] = $admininfo['name'];
            $templateVariable["title"] = __("Customer just Edited their quote, details are below.");
            $this->generateTemplate(
                $templateVariable,
                $customerinfo,
                $admininfo
            );

            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();

            //Send Mail to Closest Store
            $templateVariable['receiver_name'] = $storeInfo['name'];
            $templateVariable['title'] = __('Customer just Edited their quote, details are below.');
            $this->generateTemplate(
                $templateVariable,
                $customerinfo,
                $storeInfo

            );
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    // generate template
    protected function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $template = $this->_transportBuilder->setTemplateIdentifier($this->_tempId)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo(
                $receiverInfo['email'],
                $receiverInfo['name']
            );
        return $this;
    }

    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }

    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    public function quoteEditedByAdmin($quoteId, $message)
    {
        $quote = $this->_quoteFactory->create()->load($quoteId);

        // admin details
        $admininfo = [];
        $admininfo = [
            'name' => 'Admin',
            'email' => $this->_helper->getDefaultTransEmailId(),
        ];
        //store details
        $storeClose = $this->getClosestStore();
        $storeInfo = [];
        $storeInfo = [
            'name' => $storeClose['name'],
            'email' => substr($storeClose['email'], 0, strpos($storeClose['email'], "/")), //$storeClose['email'],
        ];

        $customer = $this->_helper->getCustomerData($quote->getCustomerId());
        $customerinfo = [];
        $customerinfo = [
            'name' => $customer->getName(),
            'email' => $customer->getEmail(),
        ];

        $this->_tempId = $this->getTemplateId(self::XML_PATH_EMAIL_QUOTE_EDIT);
        $this->_inlineTranslation->suspend();

        $templateVariable = [];
        $product = $this->_helper->getProduct($quote->getProductId());
        // Product Options  "<dl class='item-options'>".$optionPriceArr[0]."</dl>".
        $optionAndPrice = $this->_helper->getOptionNPrice($product, $quote);
        $optionPriceArr = explode('~|~', $optionAndPrice);
        $productName = '<tbody><tr>' .
            "<td class='item-info'>" . $quote->getProductName() .
            "</td><td class='item-info'>" . $product->getSku() .
            '</td></tr></tbody>';

        $templateVariable["quote_id"] = $quoteId;
        $templateVariable["product_name"] = $productName;
        $templateVariable["new_quote_qty"] = $quote->getQuoteQty();
        // $templateVariable["new_quote_price"] = $this->_helper->getformattedPrice($quote->getQuotePrice());
        $templateVariable["new_message"] = $message;
        $templateVariable["new_options"] = $optionPriceArr[0];
        $templateVariable["edit_by"] = 'admin';

        //Mail To Customer
        $templateVariable["receiver_name"] = $customerinfo['name'];
        $templateVariable["title"] = __("Admin just Edited a quote, details are below.");
        $this->generateTemplate(
            $templateVariable,
            $admininfo,
            $customerinfo
        );
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
            $this->_inlineTranslation->resume();

            //Mail To Admin
            $templateVariable["receiver_name"] = $admininfo['name'];
            $templateVariable["title"] = __("You just Edited a quote, details are below.");
            $this->generateTemplate(
                $templateVariable,
                $customerinfo,
                $admininfo
            );

            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();

            //Send Mail to Closest Store
            $templateVariable['receiver_name'] = $storeInfo['name'];
            $templateVariable['title'] = __('Admin just Edited a quote, details are below.');
            $this->generateTemplate(
                $templateVariable,
                $customerinfo,
                $storeInfo

            );
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }
}
