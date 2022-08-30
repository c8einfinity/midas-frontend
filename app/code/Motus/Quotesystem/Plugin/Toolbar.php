<?php

namespace Motus\Quotesystem\Plugin;
class Toolbar
{
    public function aroundGetAvailableOrders(
        \Magento\Catalog\Block\Product\ProductList\Toolbar $subject,
        \Closure $proceed
    ) {
        $result = $proceed();

        //make sure that each array key does exist, and then remove them
        //if (array_key_exists('position', $result)) unset($result['position']);
        //if (array_key_exists('name', $result)) unset($result['name']);
        if (array_key_exists('price', $result)) unset($result['price']);

        return $result;
    }
}