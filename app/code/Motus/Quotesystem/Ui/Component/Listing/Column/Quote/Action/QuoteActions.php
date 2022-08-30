<?php
/**
 * Quote Listing Column action Quote action
 */

namespace Motus\Quotesystem\Ui\Component\Listing\Column\Quote\Action;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class QuoteActions extends Column
{
    /**
 * Url path
*/
    const BUYERURLPATHEDIT = 'quotesystem/managequotes/edit';
    const BUYERURLPATHDELETE = 'quotesystem/managequotes/delete';

    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var string
     */
    private $_editUrl;

    /**
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
     * @param [type]             $editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $_urlBuilder,
        array $components = [],
        array $data = [],
        $_editUrl = self::BUYERURLPATHEDIT
    ) {
        $this->_urlBuilder = $_urlBuilder;
        $this->_editUrl = $_editUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param  array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['entity_id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->_urlBuilder->getUrl(
                            $this->_editUrl,
                            ['entity_id' => $item['entity_id']]
                        ),
                        'label' => __('Edit')
                    ];
                    $item[$name]['delete'] = [
                        'href' => $this->_urlBuilder->getUrl(
                            self::BUYERURLPATHDELETE,
                            ['entity_id' => $item['entity_id']]
                        ),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete quotes'),
                            'message' => __('Are you sure to delete quote(s)?')
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}
