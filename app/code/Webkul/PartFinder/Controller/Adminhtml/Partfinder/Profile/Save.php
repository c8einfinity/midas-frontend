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
namespace Webkul\PartFinder\Controller\Adminhtml\Partfinder\Profile;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Webkul\PartFinder\Model\ProfileDataFactory;
use Magento\Framework\Serialize\Serializer\Json;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var \Webkul\PartFinder\Model\ProfileDataFactory
     */
    protected $profileDataFactory;

    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        ProfileDataFactory $profileDataFactory,
        Json $json
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->profileDataFactory = $profileDataFactory;
        $this->json               = $json;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = $this->getRequest()->getParam('entity_id');
            $model = $this->profileDataFactory->create();
            if ($id) {
                $model->load($id);
            }
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Part finder no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
            try {
                $model->setId($id);
                $model->setName($data['name']);
                $model->setMapping($this->json->serialize($data['dropdown']['profile_dropdowns']));
                $model->save();
                $this->dataPersistor->clear('profile_data');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the Part finder.')
                );
            }

            if ($this->getRequest()->getParam('popup')) {
                $requestParams = [
                    'finder' => $this->getRequest()->getParam('finder'),
                    'profile' => $model->getId(),
                    '_current' => true,
                    'finder_tab' => $this->getRequest()->getParam('finder_tab'),
                ];

                return $this->returnResult(
                    'partfinder/partfinder_profile/addProfile',
                    $requestParams,
                    ['error' => false]
                );
            }
        
            $this->dataPersistor->set('partfinder_data', $data);
            return $resultRedirect->setPath(
                '*/*/edit',
                ['partfinder_id' => $this->getRequest()->getParam('entity_id')]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Create Return Result for Controller
     *
     * @param string $path
     * @param array $params
     * @param array $response
     *
     * @return Json|Redirect
     */
    protected function returnResult($path = '', array $params = [], array $response = [])
    {
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath($path, $params);
    }
}
