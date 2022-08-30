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
namespace Webkul\PartFinder\Controller\Adminhtml\Partfinder\Initialization;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Webkul\PartFinder\Api\Data\PartfinderDropdownInterfaceFactory;
use Webkul\PartFinder\Api\Data\DropdownOptionInterfaceFactory;
use Webkul\PartFinder\Model\Partfinder;
use Webkul\PartFinder\Model\Partfinder\PartfinderDropdown;

class Helper
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Webkul\PartFinder\Api\Data\PartfinderDropdownInterfaceFactory
     */
    protected $dropdownFactory;

    /**
     * @var \Webkul\PartFinder\Api\Data\DropdownOptionInterfaceFactory
     */
    protected $optionFactory;

    public function __construct(
        RequestInterface $request,
        DateTime $date,
        PartfinderDropdownInterfaceFactory $dropdownFactory,
        DropdownOptionInterfaceFactory $optionFactory
    ) {
        $this->request = $request;
        $this->date = $date;
        $this->dropdownFactory = $dropdownFactory;
        $this->optionFactory = $optionFactory;
    }

    /**
     * Intialize PartFinder Form Data
     *
     * @param Partfinder $finder
     * @param array $finderData
     * @return void
     */
    protected function initializeFromData(Partfinder $finder, array $finderData)
    {
        if ($finderData) {
            $finder->setData($finderData)
                ->setUpdatedAt($this->date->gmtDate());
            if (!$finder->getId()) {
                $finder->setCreatedAt($this->date->gmtDate());
            }
        }
        $dropdownData = $this->request->getPost('dropdown', []);
        if (isset($dropdownData['dropdowns'])) {
            $dropdowns = $dropdownData['dropdowns'];
            unset($dropdownData['options']);
        } else {
            $dropdowns = [];
        }
        if (empty($dropdowns)) {
            $finder->setStatus(0);
        }
        
        $finder = $this->fillDropdowns($finder, $dropdowns);
        return $finder;
    }

    /**
     * Initialize finder before saving
     *
     * @param \Webkul\PartFinder\Model\Partfinder $finder
     * @return \Webkul\PartFinder\Model\Partfinder
     */
    public function initialize(Partfinder $finder)
    {
        $finderData = $this->request->getParams();
        return $this->initializeFromData($finder, $finderData);
    }

    /**
     * Fills $finder with dropdowns from $finderDropdowns array
     *
     * @param Partfinder $finder
     * @param array $finderDropdowns
     * @return Partfinder
     */
    protected function fillDropdowns(Partfinder $finder, array $finderDropdowns)
    {
        if (empty($finderDropdowns)) {
            $finder->setDropdownCount(0);
            return $finder->setDropdowns([]);
        }
        $dropdowns = [];
        foreach ($finderDropdowns as $dropdownsData) {
            if (!empty($dropdownsData['is_delete'])) {
                continue;
            }
            if (empty($dropdownsData['attribute_id'])) {
                $dropdownsData['attribute_id'] = null;
            }

            if (empty($dropdownsData['option_id'])) {
                $dropdownsData['entity_id'] = null;
            } else {
                $dropdownsData['entity_id'] = $dropdownsData['option_id'];
            }
            $dropdownsData['label'] = $dropdownsData['title'];
            $dropdownsData['option_sorting'] = $dropdownsData['sorting_type'];
            $dropdownsData['is_required'] = $dropdownsData['is_require'];
            if (isset($dropdownsData['values'])) {
                $dropdownsData['values'] = array_filter($dropdownsData['values'], function ($valueData) {
                    return empty($valueData['is_delete']);
                });
            }
            $dropdownsData['is_new'] = false;
            if (!isset($dropdownsData['initialize'])) {
                $dropdownsData['is_new'] = true;
            }
            $dropdown = $this->dropdownFactory->create(['data' => $dropdownsData]);
            if (isset($dropdownsData['options'])) {
                $dropdown = $this->fillDropdownOptions($dropdown, $dropdownsData['options']);
            }
            
            $dropdowns[] = $dropdown;
        }
        $finder->setDropdownCount(count($dropdowns));
        return $finder->setDropdowns($dropdowns);
    }
    
    /**
     * Fills $dropdown with dropdown optiosns from $dropdownOptions array
     *
     * @param PartfinderDropdown $dropdown
     * @param array $dropdownOptions
     * @return PartfinderDropdown
     */
    protected function fillDropdownOptions(PartfinderDropdown $dropdown, $dropdownOptions)
    {
        if (empty($dropdownOptions)) {
            return $dropdown->setOptions([]);
        }
        
        $options = [];
        foreach ($dropdownOptions as $optionData) {
            if (!isset($optionData['entity_id'])) {
                $optionData['value'] = $optionData['id'];
            }
            $option = $this->optionFactory->create(['data' => $optionData]);
            $options[] = $option;
        }
        return $dropdown->setOptions($options);
    }
}
