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
namespace Webkul\PartFinder\Block\Adminhtml\Partfinder\Edit\Import;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Helper\Image;
use Webkul\PartFinder\Api\Data\ProfileDataInterfaceFactory;
use Magento\Framework\Serialize\Serializer\Json as JsonData;

class Profile extends Template
{
    /**
     * @var array $profile
     */
    protected $profile = [];

    /**
     * @var array $dropdowns
     */
    protected $dropdowns = [];

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    protected $locator;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $image;

    /**
     * @var \Webkul\PartFinder\Api\Data\ProfileDataInterfaceFactory
     */
    protected $profileDataFactory;
    
    public function __construct(
        Context $context,
        CurrencyInterface $localeCurrency,
        ProductRepositoryInterface $productRepository,
        LocatorInterface $locator,
        Image $image,
        ProfileDataInterfaceFactory $profileDataFactory,
        JsonData $jsonData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->localeCurrency = $localeCurrency;
        $this->productRepository = $productRepository;
        $this->locator = $locator;
        $this->image = $image;
        $this->profileDataFactory = $profileDataFactory;
        $this->jsonData = $jsonData;
    }

    /**
     * Get Profile data collection
     *
     * @return object
     */
    public function getProfileData()
    {
        $profileDataCollection = $this->profileDataFactory->create()
            ->getCollection();
        return $profileDataCollection;
    }

    /**
     * Get profile
     *
     * @return array $this->profile
     */
    public function getProfile()
    {
        if (empty($this->profile)) {
            foreach ($this->getProfileData() as $model) {
                $this->profile[] = ['id' => $model->getId(), 'label' => $model->getName()];
            }
        }
        return $this->profile;
    }

    /**
     * Get Dropdowns for Profile
     *
     * @return array $this->dropdowns
     */
    public function getProfileDropdowns()
    {
        if (empty($this->dropdowns)) {
            foreach ($this->getProfileData() as $model) {
                $this->dropdowns[$model->getId()] = $model->getMappingData();
            }
        }
        return $this->dropdowns;
    }

    /**
     * Get Add new Attribute button
     *
     * @param string $dataProvider
     * @return string
     */
    public function getAddNewProfileButton($dataProvider = '')
    {
        /** @var \Magento\Backend\Block\Widget\Button $profileCreate */
        $profileCreate = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        );
        if ($profileCreate->getAuthorization()->isAllowed('Webkul_PartFinder::profile_create')) {
            $profileCreate->setDataAttribute(
                [
                    'mage-init' => [
                        'finderProfiles' => [
                            'dataProvider' => $dataProvider,
                            'url' => $this->getUrl('partfinder/partfinder_profile/new', [
                                'store' => 0,
                                'finder_tab' => 'variations',
                                'popup' => 1,
                                '_query' => [
                                    'attribute' => [
                                        'is_global' => 1,
                                        'frontend_input' => 'select',
                                    ],
                                ],
                            ]),
                        ],
                    ],
                ]
            )->setType(
                'button'
            )->setLabel(
                __('Create New Profile')
            );
            return $profileCreate->toHtml();
        } else {
            return '';
        }
    }
    /**
     * Get Add new Attribute button
     *
     * @param string $dataProvider
     * @return string
     */
    public function getEditProfileButton($profileId = '')
    {
        /** @var \Magento\Backend\Block\Widget\Button $profileCreate */
        $profileEdit = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget::class
        );
        if ($profileEdit->getAuthorization()->isAllowed('Webkul_PartFinder::profile_create')) {
            $data = [
                'mage-init' => [
                    'finderProfiles' => [
                        'dataProvider' => $profileId,
                        'editProfile' => true,
                        'url' => $this->getUrl('partfinder/partfinder_profile/new', [
                            'store' => 0,
                            'finder_tab' => 'variations',
                            'profile_id' => $profileId,
                            'popup' => 1,
                            '_query' => [
                                'attribute' => [
                                    'is_global' => 1,
                                    'frontend_input' => 'select',
                                ],
                            ],
                        ]),
                    ],
                ],
            ];
            return $profileEdit->getButtonHtml(
                __('Edit Profile'),
                '',
                'action-additional remove-default-class',
                null,
                $data
            );
        } else {
            return '';
        }
    }

    /**
     * Retrieve data source for variations data
     *
     * @return string
     */
    public function getProvider()
    {
        return $this->getData('config/provider');
    }

    /**
     * Retrieve configurable modal name
     *
     * @return string
     */
    public function getModal()
    {
        return $this->getData('config/modal');
    }

    /**
     * Retrieve form name
     *
     * @return string
     */
    public function getForm()
    {
        return $this->getData('config/form');
    }

    /**
     * Get variation wizard using data
     *
     * @param array $initData
     * @return string
     */
    public function getVariationWizard($initData)
    {
        /** @var \Magento\Ui\Block\Component\StepsWizard $wizardBlock */
        $wizardBlock = $this->getChildBlock($this->getData('config/nameStepWizard'));
        if ($wizardBlock) {
            $wizardBlock->setInitData($initData);
            return $wizardBlock->toHtml();
        }
        return '';
    }

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
