<?php
/**
 Do you wish to enable Quote on this product.
 */

namespace Motus\Quotesystem\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Response\Http\FileFactory;

class QuotePdf
{
    /**
     * construct
     *
     * @param \Motus\Quotesystem\Model\QuotesFactory $quoteFactory
     * @param \Motus\Quotesystem\Api\QuoteDetailsRepositoryInterface $quoteDetailRepo
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Motus\Quotesystem\Model\QuotesFactory $quoteFactory,
        \Motus\Quotesystem\Api\QuoteDetailsRepositoryInterface $quoteDetailRepo,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        DateTime $date,
        FileFactory $fileFactory
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->quoteDetailRepo = $quoteDetailRepo;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->date = $date;
        $this->fileFactory = $fileFactory;
    }
    
    /**
     * generate pdf
     *
     * @param int $quoteId
     * @return pdf
     */
    public function generatePdf($quoteId)
    {
        $quoteCollection = $this->quoteFactory->create()->getCollection()
                                            ->addFieldToFilter('quote_id', $quoteId);
        $quoteDetailCollection = $this->quoteDetailRepo->getById($quoteId);
        $customerId = $quoteDetailCollection->getCustomerId();
        $customer = $this->_customerRepositoryInterface->getById($customerId);
        
        $pdf = new \Zend_Pdf();
        $pdf->pages[] = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
        $page = $pdf->pages[0]; // this will get reference to the first page.
        $style = new \Zend_Pdf_Style();
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, 15);
        $page->setStyle($style);
        $width = $page->getWidth();
        $hight = $page->getHeight();
        $x = 30;
        $pageTopalign = 850; //default PDF page height
        $this->y = 850 - 100; //print table row from page top – 100px

        // set heading
        $this->insertHeading($page, $style, $font, $x);

        // Customer Details
        $this->insertCustomerDetails($page, $style, $font, $x, $customer);
        
        // insert quote products
        $this->insertQuoteProducts($page, $style, $font, $x, $quoteCollection);

        $date = $this->date->date('Y-m-d_H-i-s');
        return $this->fileFactory->create(
            'quotation' . $date . '.pdf',
            $pdf->render(),
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }

    /**
     * insert heading
     *
     * @param \Zend_Pdf $page
     * @param \Zend_Pdf_Style $style
     * @param \Zend_Pdf_Font $font
     * @param int $x
     * @return void
     */
    public function insertHeading($page, $style, $font, $x)
    {
        $style->setFont($font, 16);
        $page->setStyle($style);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.7));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(30, $this->y + 10, $page->getWidth()-30, $this->y +70);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $style->setFont($font, 20);
        $page->setStyle($style);
        $page->drawText(__("Quote Cart Quotation"), $x + 170, $this->y+40, 'UTF-8');

        $this->y -= 90;
    }

    /**
     * insert customer details
     *
     * @param \Zend_Pdf $page
     * @param \Zend_Pdf_Style $style
     * @param \Zend_Pdf_Font $font
     * @param int $x
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customer
     * @return void
     */
    public function insertCustomerDetails($page, $style, $font, $x, $customer)
    {
        $style->setFont($font, 16);
        $page->setStyle($style);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.7));
        $page->drawRectangle(30, $this->y + 10, $page->getWidth()-30, $this->y +70);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $style->setFont($font, 15);
        $page->setStyle($style);
        $page->drawText(__("Cutomer Details"), $x + 5, $this->y+50, 'UTF-8');
        $style->setFont($font, 11);
        $page->setStyle($style);
        $page->drawText(__("Name : %1", $customer->getFirstname()), $x + 5, $this->y+33, 'UTF-8');
        $page->drawText(__("Email : %1", $customer->getEmail()), $x + 5, $this->y+16, 'UTF-8');
 
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->drawRectangle(30, $this->y -20, $page->getWidth()-30, $this->y + 5);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
    }

    /**
     * insert quote products
     *
     * @param \Zend_Pdf $page
     * @param \Zend_Pdf_Style $style
     * @param \Zend_Pdf_Font $font
     * @param int $x
     * @param \Motus\Quotesystem\Model\QuotesFactory $quoteCollection
     * @return void
     */
    public function insertQuoteProducts($page, $style, $font, $x, $quoteCollection)
    {
        $style->setFont($font, 12);
        $page->setStyle($style);
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->drawText(__("S.NO"), $x + 5, $this->y-10, 'UTF-8');
        $page->drawText(__("PRODUCT NAME"), $x + 40, $this->y-10, 'UTF-8');
        $page->drawText(__("QUOTE PRICE"), $x + 200, $this->y-10, 'UTF-8');
        $page->drawText(__("QUANTITY"), $x + 310, $this->y-10, 'UTF-8');
        $page->drawText(__("TOTAL"), $x + 440, $this->y-10, 'UTF-8');
        $totalAmount = 0;
        $this->y -= 20;
        $i = 1;
        foreach ($quoteCollection as $quote) {
            $style->setFont($font, 10);
            $page->setStyle($style);
            $add = 9;
            $page->drawText($quote->getQuotePrice(), $x + 210, $this->y-30, 'UTF-8');
            $page->drawText($quote->getQuoteQty(), $x + 330, $this->y-30, 'UTF-8');
            $totalPrice = $quote->getQuotePrice() * $quote->getQuoteQty();
            $page->drawText($totalPrice, $x + 460, $this->y-30, 'UTF-8');
            $pro = $quote->getProductName();
            $page->drawText($pro, $x + 40, $this->y-30, 'UTF-8');
            $page->drawText($i, $x + 5, $this->y-30, 'UTF-8');
            
            $totalAmount = $totalAmount + $totalPrice;
            $this->y -= 35;
            $i++;
        }
        $this->y -= 150;
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(30, $this->y -62, $page->getWidth()-30, $this->y - 100);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

        // total Amount
        $style->setFont($font, 15);
        $page->setStyle($style);
        $page->drawText(__("Total : %1", $totalAmount), $x + 435, $this->y-85, 'UTF-8');
    }
}
