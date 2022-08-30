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
 * Class ProfileLocator
 */
class ProfileLocator
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var PartfinderInterface
     */
    private $profile;

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
    public function getProfile()
    {
        if (null !== $this->profile) {
            return $this->profile;
        }
        
        if ($profile = $this->registry->registry('profile_data')) {
            return $this->profile = $profile;
        }

        throw new NotFoundException(__('Profile was not registered'));
    }
}
