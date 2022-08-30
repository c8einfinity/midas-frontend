<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_PrivateShop
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\PartFinder\Ui\DataProvider\Finder\Form\Modifier;

use Webkul\PartFinder\Model\Locator\FinderLocator;
use Magento\Framework\DB\Helper as DbHelper;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\App\CacheInterface;
use Magento\Ui\Component\Form;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * Data provider for private group field of product page
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Websites extends AbstractModifier
{

    /**
     * @param LocatorInterface $locator
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param DbHelper $dbHelper
     * @param UrlInterface $urlBuilder
     * @param ArrayManager $arrayManager
     * @param SerializerInterface $serializer
     */
    public function __construct(
        FinderLocator $locator,
        ArrayManager $arrayManager,
        StoreManagerInterface $storeManager,
        SerializerInterface $serializer = null
    ) {
        $this->locator = $locator;
        $this->storeManager = $storeManager;
        $this->arrayManager = $arrayManager;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $meta = $this->customizeWebsitesField($meta);

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

     /**
      * Customize Categories field
      *
      * @param array $meta
      * @return array
      * @since 101.0.0
      */
    protected function customizeWebsitesField(array $meta)
    {
        $fieldCode = 'website_ids';
    
        $meta = $this->meta = array_replace_recursive(
            $meta,
            [
                $fieldCode => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'additionalClasses' => 'admin__fieldset-product-websites',
                                'label' => __('Part Finder in Websites'),
                                'collapsible' => true,
                                'componentType' => Form\Fieldset::NAME,
                                'dataScope' => '',
                                'disabled' => false,
                                'sortOrder' => 100
                            ],
                        ],
                    ],
                    'children' => $this->getWebsiteFieldsForFieldset(),
                ]
            ]
        );
        return $meta;
    }

    /**
     * Prepares children for the parent fieldset
     *
     * @return array
     */
    protected function getWebsiteFieldsForFieldset()
    {
        $children = [];
        $websiteRepository = ObjectManager::getInstance()->get(WebsiteRepositoryInterface::class);
        $websiteIds = $this->getFinderWebsitesValues();
        $websitesList = $this->getWebsitesList();
        $isNewFinder = !$this->locator->getFinder()->getId();
        $tooltip = [
            'link' => 'http://docs.magento.com/m2/ce/user_guide/configuration/scope.html',
            'description' => __(
                'If your Magento installation has multiple websites, ' .
                'you can edit the scope to use the product on specific sites.'
            ),
        ];
        $sortOrder = 0;
        $label = __('Websites');

        $defaultWebsiteId = $websiteRepository->getDefault()->getId();
        foreach ($websitesList as $website) {
            $isChecked = in_array($website['id'], $websiteIds)
                || ($defaultWebsiteId == $website['id'] && $isNewFinder);
            $children[$website['id']] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => Form\Element\DataType\Number::NAME,
                            'componentType' => Form\Field::NAME,
                            'formElement' => Form\Element\Checkbox::NAME,
                            'description' => __($website['name']),
                            'tooltip' => $tooltip,
                            'sortOrder' => $sortOrder,
                            'dataScope' => 'website_ids.' . $website['id'],
                            'label' => $label,
                            'valueMap' => [
                                'true' => (string)$website['id'],
                                'false' => '0',
                            ],
                            'value' => $isChecked ? (string)$website['id'] : '0',
                        ],
                    ],
                ],
            ];

            $sortOrder++;
            $tooltip = null;
            $label = ' ';
        }

        return $children;
    }

    /**
     * Prepares websites list with groups and stores as array
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getWebsitesList()
    {
        if (!empty($this->websiteLists)) {
            return $this->websiteLists;
        }
        $this->websiteLists = [];
        $groupRepository = ObjectManager::getInstance()->get(GroupRepositoryInterface::class);
        $storeRepository = ObjectManager::getInstance()->get(StoreRepositoryInterface::class);
        $websiteRepository = ObjectManager::getInstance()->get(WebsiteRepositoryInterface::class);
        $groupList = $groupRepository->getList();
        $storesList = $storeRepository->getList();

        foreach ($websiteRepository->getList() as $website) {
            $websiteId = $website->getId();
            if (!$websiteId) {
                continue;
            }
            $websiteRow = [
                'id' => $websiteId,
                'name' => $website->getName(),
                'storesCount' => 0,
                'groups' => [],
            ];
            foreach ($groupList as $group) {
                $groupId = $group->getId();
                if (!$groupId || $group->getWebsiteId() != $websiteId) {
                    continue;
                }
                $groupRow = [
                    'id' => $groupId,
                    'name' => $group->getName(),
                    'stores' => [],
                ];
                foreach ($storesList as $store) {
                    $storeId = $store->getId();
                    if (!$storeId || $store->getStoreGroupId() != $groupId) {
                        continue;
                    }
                    $websiteRow['storesCount']++;
                    $groupRow['stores'][] = [
                        'id' => $storeId,
                        'name' => $store->getName(),
                    ];
                }
                $websiteRow['groups'][] = $groupRow;
            }
            $this->websiteLists[] = $websiteRow;
        }

        return $this->websiteLists;
    }

    /**
     * Return array of websites ids, assigned to the fidner
     *
     * @return array
     */
    protected function getFinderWebsitesValues()
    {
        return $this->locator->getFinder()->getWebsiteIds();
    }
}
