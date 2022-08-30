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
namespace Webkul\PartFinder\Block\Widget;
 
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Message\ManagerInterface;
use Webkul\PartFinder\Api\PartfinderRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json as JsonData;

class Partfinder extends Template implements BlockInterface
{
    /**
     * @var array $finder
     */
    protected $finder = [];

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Webkul\PartFinder\Api\PartfinderRepositoryInterface
     */
    protected $partFinderRepository;

    /**
     * @param Context $context
     * @param ManagerInterface $messageManager
     * @param PartfinderRepositoryInterface $partFinderRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        ManagerInterface $messageManager,
        PartfinderRepositoryInterface $partFinderRepository,
        JsonData $jsonData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->messageManager = $messageManager;
        $this->partFinderRepository = $partFinderRepository;
        $this->jsonData = $jsonData;
    }

    /**
     * Get Finder by id
     *
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface
     */
    public function getFinder()
    {
        $finderId = $this->getData('finder_id');
        try {
            if ($finderId && !isset($this->finder[$finderId])) {
                $this->finder[$finderId] = $this->partFinderRepository->getById($finderId);
            }
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addNotice(
                __($e->getMessage())
            );
            return false;
        }
        
        return $this->finder[$finderId];
    }

    /**
     * Get dropdowns for part finder
     *
     * @param int $finderId
     * @return void
     */
    public function getDropdowns()
    {
        $finder = $this->getFinder();
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
    public function getDropdownOptions()
    {
        $dropdownData = [];
        if ($this->getDropdowns()) {
            foreach ($this->getDropdowns() as $dropdown) {
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
                $dropdownOptions = $this->sortOptions($dropdown, $dropdownOptions);
                $dropdownData[] = [
                    'dropdown_id' => $dropdown->getId(),
                    'sort_order' => $dropdown->getSortOrder(),
                    'label' => $dropdown->getLabel(),
                    'options' => $dropdownOptions,
                    'is_required' => (bool) $dropdown->getIsRequired()
                ];
            }
        }
        
        return $dropdownData;
    }

    /**
     * Get part finder product variations
     *
     * @return array
     */
    public function getVariations()
    {
        $tempVariations = [];
        $variations = [];
        $finder = $this->getFinder();
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
