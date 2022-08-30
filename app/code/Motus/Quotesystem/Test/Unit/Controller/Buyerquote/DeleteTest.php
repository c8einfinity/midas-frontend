<?php
/**
 * Delete Quote test case.
 */
namespace Motus\Quotesystem\Test\Unit\Controller\Buyerquote;

use Motus\Quotesystem\Controller\Buyerquote\Delete;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class DeleteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Delete
     */
    protected $_deleteController;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $_context;
    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sessionMock;
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resultRedirectFactory;
    /**
     * @var \Motus\Quotesystem\Api\QuoteRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_quoteRepositoryMock;
    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;
    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_messageManager;
    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resultRedirect;

    protected function setUp()
    {
        $this->_request = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->getMockForAbstractClass();
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
        $this->_deleteController = new Delete(
            $this->_context,
            $this->_sessionMock,
            $pageFactoryMock,
            $this->_quoteRepositoryMock
        );
    }

    public function testExecute()
    {
        $quoteId = 1000;
        $this->_request->expects($this->once())
            ->method('getParam')
            ->with('id', false)
            ->willReturn($quoteId);
        $this->_quoteRepositoryMock->expects($this->once())
            ->method('deleteById')
            ->with($quoteId);
        $this->_messageManager->expects($this->once())
            ->method('addSuccess')
            ->with(__('Quote is successfully deleted.'));
        $this->_resultRedirectFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->_resultRedirect);
        $this->_resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/index')
            ->willReturnSelf();
        $this->assertSame($this->_resultRedirect, $this->_deleteController->execute());
    }
}
