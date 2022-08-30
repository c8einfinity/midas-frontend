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
namespace Webkul\PartFinder\Model\ProfileData;
 
use Magento\Framework\Data\OptionSourceInterface;
use Webkul\PartFinder\Model\ResourceModel\ProfileData\CollectionFactory;
use Magento\Framework\App\RequestInterface;
 
/**
 * Profiles tree for "Profiles" field
 */
class Profiles implements OptionSourceInterface
{
    /**
     * @var \Webkul\PartFinder\Model\ResourceModel\ProfileData\CollectionFactory
     */
    protected $collectionFactory;
 
    /**
     * @var RequestInterface
     */
    protected $request;
 
    /**
     * @var array
     */
    protected $profilesTree;
 
    /**
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        RequestInterface $request
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->request = $request;
    }
 
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getProfileTree();
    }
 
    /**
     * Retrieve profiles tree
     *
     * @return array
     */
    protected function getProfileTree()
    {
        if ($this->profilesTree === null) {
            $collection = $this->collectionFactory->create();
            $collection->addFieldToSelect('*');
            $profilesById = [];
            foreach ($collection as $profileData) {
                $profileId = $profileData->getEntityId();
                if (!isset($profilesById[$profileId])) {
                    $profilesById[$profileId] = [
                        'value' => $profileId
                    ];
                }
                $profilesById[$groupId]['label'] = $profileData->getName();
            }
            $this->profilesTree = $profilesById;
        }
        
        return $this->profilesTree;
    }
}
