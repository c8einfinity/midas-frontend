<?php
/**
 * Delete Quote test case.
 */
namespace Motus\Quotesystem\Test\Unit\Controller\Buyerquote;

use Motus\Quotesystem\Controller\Buyerquote\Savequote;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class SavequoteTest extends \PHPUnit_Framework_TestCase
{
    /** @var Delete */
    protected $_saveController;

    /** @var \Magento\Framework\App\Action\Context */
    protected $_context;
    /** @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $_sessionMock;
    /** @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_resultRedirectFactory;
    /** @var \Motus\Quotesystem\Api\QuoteRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_quoteRepositoryMock;
    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_request;
    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_messageManager;
    /** @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject */
    protected $_resultRedirect;
    /** @var Motus\Quotesystem\Model\QuotesFactory */
    protected $_quotesFactory;
    /** @var Motus\Quotesystem\Helper\Data */
    protected $_quoteHelper;
    /** @var Motus\Quotesystem\Helper\Mail*/
    protected $_quoteMailHelper;

    protected $_productFactory;

    protected function setUp()
    {
        $this->_request = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->getMockForAbstractClass();
        $this->_productFactory = $this->getMockBuilder('Magento\Catalog\Model\ProductFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_messageManager = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->getMockForAbstractClass();
        $pageFactoryMock = $this->getMockBuilder('Magento\Framework\View\Result\PageFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManagerHelper($this);
        $this->_sessionMock = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_quoteRepositoryMock = $this->getMockBuilder('Motus\Quotesystem\Api\QuoteRepositoryInterface')
            ->getMockForAbstractClass();
        $this->_resultRedirect = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_quotesFactory = $this->getMockBuilder('Motus\Quotesystem\Model\QuotesFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_quoteHelper = $this->getMockBuilder('Motus\Quotesystem\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_quoteMailHelper = $this->getMockBuilder('Motus\Quotesystem\Helper\Mail')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_resultRedirectFactory = $this->getMockBuilder('Magento\Framework\Controller\Result\RedirectFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_context = $objectManager->getObject(
            'Magento\Framework\App\Action\Context',
            [
                'request' => $this->_request,
                'messageManager' => $this->_messageManager,
                'resultRedirectFactory' => $this->_resultRedirectFactory,
            ]
        );
        $this->_saveController = new Savequote(
            $this->_context,
            $this->_productFactory,
            $this->_sessionMock,
            $this->_quotesFactory,
            $this->_quoteMailHelper,
            $this->_quoteHelper,
            $this->_quoteRepositoryMock,
            $pageFactoryMock
        );
    }

    /**
     */
    public function testGetBundleProductData()
    {
        $bundleOption = [];
        $params = ['bundle_option' => [3=>5], 'bundle_option_qty' => []];
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
                ->disableOriginalConstructor()
                ->getMock();
        $bundleOption['bundle_option'] = $params['bundle_option'];
        if (isset($params['bundle_option_qty'])) {
            $bundleOption['bundle_option_qty'] = $params['bundle_option_qty'];
        }
        $result = $this->_saveController->getBundleProductData($params, $productMock);
        $this->assertEquals($bundleOption, $result);
    }

    public function testGetBundleProductDataIfProductOfDifferentType()
    {
        $bundleOption = [];
        $params = ['bundle_option' => [3=>5], 'bundle_option_qty' => []];
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
                ->disableOriginalConstructor()
                ->getMock();
        $productMock->expects($this->any())
            ->method('getId')
            ->willReturn(333);
        $bundleOption['bundle_option'] = $params['bundle_option'];
        if (isset($params['bundle_option_qty'])) {
            $bundleOption['bundle_option_qty'] = $params['bundle_option_qty'];
        }
        $result = $this->_saveController->getBundleProductData($params, $productMock);
        $this->assertEquals($bundleOption, $result);
    }
    
    public function testGetBundleWithoutParam()
    {
        $storeId = 1;
        $bundleOption = ['bundle_option' => [10=>311]];
        $params = ['bundle_option' => [10=>311]];
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
                ->disableOriginalConstructor()
                ->getMock();
        $productMock->expects($this->any())
            ->method('getEntityId')
            ->willReturn(333);
        $productMock->expects($this->any())
            ->method('getTypeId')
            ->willReturn('bundle');
        $productTypeMock = $this->getMockBuilder('\Magento\Bundle\Model\Product\Type')
            ->setMethods(['setStoreFilter', 'getOptionsCollection', 'getOptionsIds', 'getSelectionsCollection'])
            ->disableOriginalConstructor()
            ->getMock();
        $option1 = $this->getRequiredOptionMock(10, 10, 311);
        $option2 = $this->getRequiredOptionMock(20, 10, 312);
        $option3 = $this->getRequiredOptionMock(30, 10, 313);

        $optionCollectionMock = $this->getOptionCollectionMock([$option1, $option2, $option3]);

        $selectionCollectionMock = $this->getSelectionCollectionMock([$option1, $option2]);
        $productMock->expects($this->any())
            ->method('getTypeInstance')
            ->willReturn($productTypeMock);
        $productTypeMock->expects($this->once())
            ->method('getOptionsIds')
            ->willReturn([10,20,30]);

        $productTypeMock->expects($this->once())
            ->method('getSelectionsCollection')
            ->willReturn($selectionCollectionMock);

        $productMock->expects($this->any())
            ->method('getData')
            ->willReturnCallback(
                function ($key) use ($optionCollectionMock, $selectionCollectionMock) {
                    $resultValue = null;
                    switch ($key) {
                        case '_cache_instance_selections_collection10_20_30':
                            $resultValue = $selectionCollectionMock;
                            break;
                        case '_cache_instance_options_collection':
                            $resultValue = $optionCollectionMock;
                            break;
                    }
                    return $resultValue;
                }
            );

        foreach ($bundleOption['bundle_option'] as $optionkey => $optionValue) {
            foreach ($selectionCollectionMock->getItems() as $selectionValue) {
                $selectedOption = $selectionValue->getOptionId();
                $selectionId = $selectionValue->getSelectionId();
                $selectionQty = $selectionValue->getSelectionQty();
                if ($selectedOption == $optionkey && $selectionId == $optionValue) {
                    $bundleOption['bundle_option_qty'][$optionkey] = $selectionQty;
                }
            }
        }
        $result = $this->_saveController->getBundleProductData($params, $productMock);
        $this->assertEquals($bundleOption, $result);
    }
    private function getSelectionCollectionMock(array $selectedOptions)
    {
        $selectionCollectionMock = $this->getMockBuilder(
            '\Magento\Bundle\Model\ResourceModel\Selection\Collection'
        )
            ->setMethods(['getItems', 'getIterator'])
            ->disableOriginalConstructor()
            ->getMock();

        $selectionCollectionMock
            ->expects($this->any())
            ->method('getItems')
            ->willReturn($selectedOptions);

        $selectionCollectionMock
            ->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($selectedOptions));

        return $selectionCollectionMock;
    }
    private function getRequiredOptionMock($id, $selectionQty, $selectionId)
    {
        $option = $this->getMockBuilder('Magento\Bundle\Model\Option')
            ->setMethods(
                [
                    'getRequired',
                    'isSalable',
                    'hasSelectionQty',
                    'getSelectionQty',
                    'getOptionId',
                    'getId',
                    'getSelectionId',
                    'getSelectionCanChangeQty'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $option->method('getRequired')
            ->willReturn(true);
        $option->method('isSalable')
            ->willReturn(true);
        $option->method('hasSelectionQty')
            ->willReturn(true);
        $option->method('getSelectionQty')
            ->willReturn($selectionQty);
        $option->method('getOptionId')
            ->willReturn($id);
        $option->method('getSelectionCanChangeQty')
            ->willReturn(false);
        $option->method('getId')
            ->willReturn($id);
        $option->method('getSelectionId')
            ->willReturn($selectionId);
        return $option;
    }
    private function getOptionCollectionMock(array $options)
    {
        $ids = [];
        foreach ($options as $option) {
            $ids[] = $option->getId();
        }

        $optionCollectionMock = $this->getMockBuilder('\Magento\Bundle\Model\ResourceModel\Option\Collection')
            ->setMethods(['getItems', 'getAllIds'])
            ->disableOriginalConstructor()
            ->getMock();

        $optionCollectionMock
            ->expects($this->any())
            ->method('getItems')
            ->willReturn($options);

        $optionCollectionMock
            ->expects($this->any())
            ->method('getAllIds')
            ->willReturn($ids);

        return $optionCollectionMock;
    }
}
