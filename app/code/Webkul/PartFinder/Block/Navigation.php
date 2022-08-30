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
namespace Webkul\PartFinder\Block;
 
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Webkul\PartFinder\Api\PartfinderRepositoryInterface;
use Webkul\PartFinder\Model\ResourceModel\PartfinderCategory\CollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json as JsonData;

class Navigation extends Template
{
    /**
     * @var array $finders
     */
    protected $finders = [];

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Webkul\PartFinder\Api\PartfinderRepositoryInterface
     */
    protected $partFinderRepository;
    
    /**
     * @var \Webkul\PartFinder\Model\ResourceModel\PartfinderCategory\CollectionFactory
     */
    protected $partfinderCategoryCollectionFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param PartfinderRepositoryInterface $partFinderRepository
     * @param CollectionFactory $partfinderCategoryCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        PartfinderRepositoryInterface $partFinderRepository,
        CollectionFactory $partfinderCategoryCollectionFactory,
        JsonData $jsonData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->partFinderRepository = $partFinderRepository;
        $this->collection = $partfinderCategoryCollectionFactory->create();
        $this->jsonData = $jsonData;
    }

    /**
     * Get current category
     *
     * @return Category
     */
    public function getCategory()
    {
        return $this->registry->registry('current_category');
    }

    /**
     * Apply layer
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        if ($this->getCategory()) {
            $categoryId = $this->getCategory()->getId();
            $partfinderCategoryCollection = $this->collection->addFieldToFilter(
                'category_id',
                ['eq' => $categoryId]
            );
            if ($partfinderCategoryCollection->getSize()) {
                foreach ($partfinderCategoryCollection as $partfinderCategory) {
                    try {
                        $this->finders[$partfinderCategory->getFinderId()] = $this->partFinderRepository->getById(
                            $partfinderCategory->getFinderId()
                        );
                    } catch (NoSuchEntityException $e) {
                        $this->finders[$partfinderCategory->getFinderId()] = false;
                    }
                }
            }
        }
        return parent::_prepareLayout();
    }

    /**
     * Get Finder by id
     *
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface
     */
    public function getFinders()
    {
        if (!empty($this->finders)) {
            return $this->finders;
        }
        return false;
    }

    /**
     * Get dropdowns for part finder
     *
     * @param int $finderId
     * @return void
     */
    public function getDropdowns($finderId)
    {
        $finder = $this->finders[$finderId];
        if ($finder) {
            $items = $finder->getDropdownsCollection();
            return $items->getItems();
        }
        return false;
    }

    /**
     * Sort dropdown options
     *
     * @param \Webkul\PartFinder\Api\PartfinderDropdownInterface $dropdown
     * @param array $options
     * @return array
     */
    protected function sortOptions($dropdown, array $options)
    {
        $sortOrder = explode('-', $dropdown->getOptionSorting());
       
        if ($sortOrder[1] === 'asc') {
            usort($options, function ($option1, $option2) {
                return strnatcmp($option1['label'], $option2['label']);
            });
        } else {
            usort($options, function ($option1, $option2) {
                return strnatcmp($option2['label'], $option1['label']);
            });
        }
        return $options;
    }

    /**
     * Get Dropdown options
     *
     * @return array
     */
    public function getDropdownOptions($finderId)
    {
        $dropdownData = [];
        if ($this->getDropdowns($finderId)) {
            foreach ($this->getDropdowns($finderId) as $dropdown) {
                $dropdownOptions = [];
                if (!empty($dropdown->getOptions())) {
                    foreach ($dropdown->getOptions() as $option) {
                        $dropdownOptions[] = [
                            'option_id' => $option->getId(),
                            'value' => $option->getValue(),
                            'label' => $option->getLabel()
                        ];
                    }
                } else {
                    continue;
                }
                
                $dropdownOptions = $this->sortOptions($dropdown, $dropdownOptions);
                $dropdownData[] = [
                    'dropdown_id' => $dropdown->getId(),
                    'sort_order' => $dropdown->getSortOrder(),
                    'label' => $dropdown->getLabel(),
                    'options' => $dropdownOptions,
                    'is_required' => (bool)$dropdown->getIsRequired()
                ];
            }
        }
        
        return $dropdownData;
    }

    /**
     * Get part finder product variations
     *
     * @param int $finderId
     * @return array
     */
    public function getVariations($finderId)
    {
        $tempVariations = [];
        $variations = [];
        $finder = $this->finders[$finderId];
        if ($finder) {
            $items = $finder->getManualSelectionCollection();
            foreach ($items as $selection) {
                $variationKeys = explode('-', $selection->getVariationKey());
                foreach ($variationKeys as $key => $value) {
                    if (isset($variationKeys[$key+1])) {
                        $variations[$value][] = $variationKeys[$key+1];
                    }
                }
            }
            
            foreach ($variations as $key => $value) {
                $unique = array_unique($value);
                $variations[$key] = array_values($unique);
            }
        }
        return $variations;
    }

    /**
     * Return current URL with rewrites and additional parameters
     *
     * @param array $params Query parameters
     * @return string
     */
    public function getPagerUrl($params = [])
    {
        $urlParams = [];
        $urlParams['_current'] = true;
        $urlParams['_escape'] = false;
        $urlParams['_use_rewrite'] = true;
        $urlParams['_query'] = $params;
        return $this->getUrl('*/*/*', $urlParams);
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
