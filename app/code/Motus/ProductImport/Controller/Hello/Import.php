<?php
/**
 * MageHelper Print Hello World Simple module
 *
 * @package      MageHelper_PrintHelloWorld
 * @author       Kishan Savaliya <kishansavaliyakb@gmail.com>
 */

namespace Motus\ProductImport\Controller\Product;

class World extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context)
    {
        return parent::__construct($context);
    }

    public function execute()
    {
        echo 'Motus - Import Products from Central Product Database';
        die();
    }
}
