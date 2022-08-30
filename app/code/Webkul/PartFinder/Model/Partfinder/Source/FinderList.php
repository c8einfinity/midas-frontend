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
namespace Webkul\PartFinder\Model\Partfinder\Source;

use Webkul\PartFinder\Model\ResourceModel\Partfinder\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Partfinder list model
 *
 * @api
 */
class FinderList implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions()
    {
        $result = [];
        $result[] = ['value' => '', 'label' => __('Select....')];
        foreach ($this->collectionFactory->create() as $finder) {
            $result[] = ['value' => $finder->getId(), 'label' => $finder->getWidgetName()];
        }

        return $result;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
