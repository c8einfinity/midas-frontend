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
namespace Webkul\PartFinder\Block\Adminhtml\Partfinder\Finder;

use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Template\Context;
use Webkul\PartFinder\Model\ResourceModel\Partfinder\CollectionFactory;
use Webkul\PartFinder\Api\DropdownOptionRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonData;

class FinderContent extends Widget
{
    /**
     * @var string
     */
    protected $_template = 'finder/content.phtml';

    /**
     * @var array
     */
    protected $finderDropdowns = [];

    /**
     * @var array
     */
    protected $partFinders = [];

    /**
     * @var \Webkul\PartFinder\Model\ResourceModel\Partfinder\CollectionFactory
     */
    protected $partFinderCollectionFactory;

    /**
     * @var \Webkul\PartFinder\Api\DropdownOptionRepositoryInterface
     */
    protected $dropdownOptionRepository;

    /**
     * @param Context $context
     * @param CollectionFactory $partFinderCollectionFactory
     * @param DropdownOptionRepositoryInterface $dropdownOptionRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $partFinderCollectionFactory,
        DropdownOptionRepositoryInterface $dropdownOptionRepository,
        JsonData $jsonData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->partFinderCollectionFactory = $partFinderCollectionFactory;
        $this->dropdownOptionRepository = $dropdownOptionRepository;
        $this->jsonData = $jsonData;
    }

    /**
     * @return string
     */
    public function getJsObjectName()
    {
        return $this->getHtmlId() . 'JsObject';
    }

    /**
     * Get Part-Finders Data
     *
     * @return array
     */
    public function getPartFinders()
    {
        $partFinders = [];
        $partFinderCollection = $this->partFinderCollectionFactory->create()->load();
        foreach ($partFinderCollection as $partFinder) {
            if ($partFinder->getDropdownCount()) {
                $this->finderDropdowns[$partFinder->getId()] = $partFinder->getDropdowns();
            }
            $this->partFinders[$partFinder->getId()] = $partFinder;
            $partFinders[] = [
                'entity_id' => $partFinder->getId(),
                'name' => $partFinder->getName()
            ];
        }
        return $partFinders;
    }

    /**
     * Get product variations for each finder
     *
     * @param \Webkul\PartFinder\Model\Partfinder $finder
     * @return array
     */
    public function getVarations($finder, $dropdownId)
    {
        
        $variations = [];
        $productId = $this->getElement()->getDataObject()->getData('entity_id');
        if ($productId) {
            $selections = $finder->getManualSelectionCollection()
                ->addFieldToFilter('product_id', ['eq' => $productId]);
            
            foreach ($selections as $selection) {
                $variationKey = $selection->getVariationKey();
                if (!empty($variationKey)) {
                    $keys = explode('-', $variationKey);
                    $optionValue = [];
                    foreach ($keys as $value) {
                        $dropdownOptionModel = $this->dropdownOptionRepository->getById($value);
                        if ($dropdownOptionModel->getDropdownId() == $dropdownId) {
                            $variations[$dropdownId][] = $dropdownOptionModel->getValue();
                        }
                    }
                }
            }
            return isset($variations[$dropdownId])?array_unique($variations[$dropdownId]):[];
        }
        return $variations;
    }

    /**
     * Return Part finder dropdowns
     *
     * @return array
     */
    public function getDropdowns()
    {
        $dropdownData = [];
        foreach ($this->finderDropdowns as $dropdowns) {
            if (is_array($dropdowns)) {
                foreach ($dropdowns as $dropdown) {
                    $dropdownOptions = [];
                    if (empty($dropdown->getOptions())) {
                        continue;
                    }
                    foreach ($dropdown->getOptions() as $option) {
                        $dropdownOptions[] = [
                            'option_id' => $option->getId(),
                            'value' => $option->getValue(),
                            'label' => $option->getLabel()
                        ];
                    }
                    $dropdownData[$dropdown->getFinderId()][] = [
                        'dropdown_id' => $dropdown->getId(),
                        'label' => $dropdown->getLabel(),
                        'options' => $dropdownOptions,
                        'choosen' => $this->getVarations(
                            $this->partFinders[$dropdown->getFinderId()],
                            $dropdown->getId()
                        ),
                        'is_required' => (bool) $dropdown->getIsRequired()
                    ];
                }
            }
        }
        return $dropdownData;
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
