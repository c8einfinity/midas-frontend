<?php
/**
 Do you wish to enable Quote on this product.
 */

namespace Motus\Quotesystem\Controller\Buyerquote;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File as DriverFile;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Quotesystem Quote File Delete controller.
 */
class FileDelete extends Action
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $_mediaDirectory;

    /**
     * @var DriverFile
     */
    private $driverFile;

    /**
     * JsonHelper.
     *
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * @param Context    $context
     * @param Filesystem $filesystem
     * @param DriverFile $driverFile
     * @param JsonHelper $jsonHelper
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        DriverFile $driverFile,
        JsonHelper $jsonHelper
    ) {
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(
            DirectoryList::MEDIA
        );
        $this->driverFile = $driverFile;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $fileName = $this->getRequest()->getParam('file_name');
            $target = $this->_mediaDirectory->getAbsolutePath('motquote\files');
            $resultData['error'] = 1;
            if ($this->driverFile->isExists($target.$fileName)) {
                $this->driverFile->deleteFile($target.$fileName);
                $resultData['error'] = 0;
            }
            $this->getResponse()->representJson(
                $this->jsonHelper->jsonEncode($resultData)
            );
        } catch (\Exception $e) {
            $this->getResponse()->representJson(
                $this->jsonHelper->jsonEncode(
                    [
                        'error' => $e->getMessage(),
                        'errorcode' => $e->getCode(),
                    ]
                )
            );
        }
    }
}
