<?php
namespace C8EEE\Parcelninja\Model\Config\Source;

class DeliveryMethod implements \Magento\Framework\Option\ArrayInterface
{
 public function toOptionArray()
 {
  return [
    ['value' => 'normal', 'label' => __('Normal')],
    ['value' => 'cheapest', 'label' => __('Cheapest')],
    ['value' => 'fastest', 'label' => __('Fastest')]
  ];
 }
}
