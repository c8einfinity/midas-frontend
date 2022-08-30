<?php
namespace Motus\Tools\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Motus\Tools\Block\MotusLocationTrait;


class StoresLocator extends Template implements BlockInterface
{

    protected $_template = "widget/stores_locator.phtml";
    protected $_locationUrl = "https://maps.googleapis.com/maps/api/geocode/json";


    public function getStoreList($latitude, $longitude , $distance=100) {
        $this->initTina4();
        $stores = $this->_DBA->fetch ("select id, name, address,email,telephone, latitude, longitude, country, region, week_times, saturday_times, sunday_times, holiday_times, round(st_distance_sphere( point({$latitude}, {$longitude}), point(ms.latitude, ms.longitude)) / 1000 , 2) as km
                                       from motus_store ms
                                       where is_active = 1 and (st_distance_sphere( point({$latitude}, {$longitude}), point(ms.latitude, ms.longitude)) / 1000 < {$distance})
                                       order by 14 ", 10)->asObject();

        return $stores;
    }

    use MotusLocationTrait;
}
