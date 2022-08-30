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
namespace Webkul\PartFinder\Model\Locator;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;
use Webkul\PartFinder\Api\Data\PartfinderInterface;

/**
 * Class FinderLocator
 */
class FinderLocator
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var PartfinderInterface
     */
    private $finder;

    /**
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     * @throws NotFoundException
     */
    public function getFinder()
    {
        if (null !== $this->finder) {
            return $this->finder;
        }
        
        if ($finder = $this->registry->registry('webkul_partfinder')) {
            return $this->finder = $finder;
        }

        throw new NotFoundException(__('Finder was not registered'));
    }
}
