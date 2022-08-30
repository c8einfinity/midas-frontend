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
namespace Webkul\PartFinder\Model\ResourceModel\Partfinder\Website;

use Webkul\PartFinder\Api\Data\PartfinderInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;

class Link
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * Link constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Retrieve associated with finder websites ids
     * @param int $finderId
     * @return array
     */
    public function getWebsiteIdsByFinderId($finderId)
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()->from(
            $this->getFinderWebsiteTable(),
            'website_id'
        )->where(
            'finder_id = ?',
            (int) $finderId
        );

        return $connection->fetchCol($select);
    }

    /**
     * Return true - if websites was changed, and false - if not
     * @param PartfinderInterface $finder
     * @param array $websiteIds
     * @return bool
     */
    public function saveWebsiteIds(PartfinderInterface $finder, array $websiteIds)
    {
        $connection = $this->resourceConnection->getConnection();

        $oldWebsiteIds = $this->getWebsiteIdsByFinderId($finder->getId());
        $insert = array_diff($websiteIds, $oldWebsiteIds);
        $delete = array_diff($oldWebsiteIds, $websiteIds);

        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $websiteId) {
                $data[] = ['finder_id' => (int) $finder->getId(), 'website_id' => (int) $websiteId];
            }
            $connection->insertMultiple($this->getFinderWebsiteTable(), $data);
        }

        if (!empty($delete)) {
            foreach ($delete as $websiteId) {
                $condition = ['finder_id = ?' => (int) $finder->getId(), 'website_id = ?' => (int) $websiteId];
                $connection->delete($this->getFinderWebsiteTable(), $condition);
            }
        }

        if (!empty($insert) || !empty($delete)) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    private function getFinderWebsiteTable()
    {
        return $this->resourceConnection->getTableName('partfinder_website');
    }
}
