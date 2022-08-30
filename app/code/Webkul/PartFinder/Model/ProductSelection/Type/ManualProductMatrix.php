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
namespace Webkul\PartFinder\Model\ProductSelection\Type;

class ManualProductMatrix
{

    /**
     * @param \Webkul\PartFinder\Api\Data\PartfinderInterface $finder
     * @return void
     */
    public function getManualVariations(\Webkul\PartFinder\Api\Data\PartfinderInterface $finder)
    {
        $variations = [];
        if ($finder->getId()) {
            $collection = $finder->getManualSelectionCollection();
            foreach ($collection as $model) {
                $variations[] = $model->getData();
            }
        }
        return $variations;
    }
}
