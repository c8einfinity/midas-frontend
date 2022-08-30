<?php
/**
 * Quote Options
 */

namespace Motus\Quotesystem\Ui\Component\MassAction\Status;

use Magento\Framework\UrlInterface;
use Zend\Stdlib\JsonSerializable;

/**
 * Class Options
 */
class QuoteOptions implements JsonSerializable
{
    /**
     * @var array
     */
    protected $_options;
    /**
     * Additional options params
     *
     * @var array
     */
    protected $_data;
    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;
    /**
     * Base URL for subactions
     *
     * @var string
     */
    protected $_urlPath;
    /**
     * Param name for subactions
     *
     * @var string
     */
    protected $_paramName;
    /**
     * Additional params for subactions
     *
     * @var array
     */
    protected $_additionalData = [];

    /**
     * Constructor
     *
     * @param UrlInterface $urlBuilder
     * @param array        $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->_data = $data;
        $this->_urlBuilder = $urlBuilder;
    }

    /**
     * Get action options
     *
     * @return array
     */
    public function jsonSerialize()
    {
        if ($this->_options === null) {
            $options = $this->getOptionArray();
            $this->prepareData();
            foreach ($options as $option) {
                $this->_options[$option['value']] = [
                    'type' => 'quote_status_' . $option['value'],
                    'label' => $option['label'],
                ];
                $this->_options[$option['value']]['url'] = $this->_urlBuilder->getUrl(
                    $this->_urlPath,
                    [$this->_paramName => $option['value']]
                );
                $this->_options[$option['value']] = array_merge_recursive(
                    $this->_options[$option['value']],
                    $this->_additionalData
                );
            }
            $this->_options = array_values($this->_options);
        }
        return $this->_options;
    }

    /**
     * Prepare addition data for subactions
     *
     * @return void
     */
    protected function prepareData()
    {
        foreach ($this->_data as $key => $value) {
            switch ($key) {
                case 'urlPath':
                    $this->_urlPath = $value;
                    break;
                case 'paramName':
                    $this->_paramName = $value;
                    break;
                default:
                    $this->_additionalData[$key] = $value;
                    break;
            }
        }
    }

    public function getOptionArray()
    {
        $options = [[
            'value' => 2,
            'label' => __('Approved')
        ], [
            'value' => 3,
            'label' => __('Declined')
        ]
        ];
        return $options;
    }
}
