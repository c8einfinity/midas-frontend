<?php
/**
 Do you wish to enable Quote on this product.
 */

namespace Motus\Quotesystem\Controller\Buyerquote;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Quotesystem Quote File Upload controller.
 */
class FileUpload extends Action
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $_mediaDirectory;

    /**
     * File Uploader factory.
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    private $_fileUploaderFactory;

    /**
     * JsonHelper.
     *
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * @param Context                                          $context
     * @param Filesystem                                       $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param JsonHelper                                       $jsonHelper
     * @param \Motus\Quotesystem\Helper\Data                  $helper
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        JsonHelper $jsonHelper,
        \Motus\Quotesystem\Helper\Data $helper
    ) {
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(
            DirectoryList::MEDIA
        );
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $errors = $this->helper->validateFiles($this->getRequest()->getFiles());
            if (empty($errors)) {
                $target = $this->_mediaDirectory->getAbsolutePath('motquote\files');
                $fileUploader = $this->_fileUploaderFactory->create(
                    ['fileId' => 'files']
                );
                $allowedType = $this->helper->getAllowedFileTypes();
                $allowedExtensions = [];
                if ($allowedType) {
                    $allowedExtensions = explode(',', $allowedType);
                }
                if (empty($allowedExtensions)) {
                    $allowedExtensions = ['gif', 'jpg', 'png', 'jpeg', 'pdf', 'doc', 'zip'];
                }
                $fileUploader->validateFile();
                $fileUploader->setAllowedExtensions(
                    $allowedExtensions
                );
                $fileUploader->setFilesDispersion(true);
                $fileUploader->setAllowRenameFiles(true);
                $resultData = $fileUploader->save($target);
                unset($resultData['tmp_name']);
                unset($resultData['path']);
                $resultData['extension'] = $resultData['file'];
                $this->getResponse()->representJson(
                    $this->jsonHelper->jsonEncode($resultData)
                );
            } else {
                foreach ($errors as $key => $errorMsg) {
                    $this->messageManager->addError($errorMsg);
                    $this->getResponse()->representJson(
                        $this->jsonHelper->jsonEncode(
                            [
                            'error' => $errorMsg,
                            'errorcode' => 1
                            ]
                        )
                    );
                }
            }
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
