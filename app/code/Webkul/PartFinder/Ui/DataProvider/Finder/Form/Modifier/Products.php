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
namespace Webkul\PartFinder\Ui\DataProvider\Finder\Form\Modifier;

use Magento\Catalog\Model\Product\Attribute\Backend\Sku;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Modal;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Webkul\PartFinder\Model\Locator\FinderLocator;
use Webkul\PartFinder\Ui\DataProvider\Finder\Form\Modifier\Data\FinderProducts;

/**
 * Data provider for products of part finder form
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */

class Products extends AbstractModifier
{
    const GROUP_FINDERPRODUCTS = 'products';
    const MANUAL_PRODUCT_MODAL = 'manual_product_modal';
    const FINDER_MANUAL_PRODUCT_LISTING = 'finder_product_listing';
    const CONFIGURABLE_MATRIX = 'configurable-matrix';

    /**
     * @var string
     */
    private static $groupContent = 'content';

    /**
     * @var int
     */
    private static $sortOrder = 30;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var string
     */
    private $formName;

    /**
     * @var string
     */
    private $dataScopeName;

    /**
     * @var string
     */
    private $dataSourceName;

    /**
     * @var string
     */
    private $associatedListingPrefix;

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @param FinderLocator $locator
     * @param FinderProducts $finderProducts
     * @param UrlInterface $urlBuilder
     * @param [type] $formName
     * @param [type] $dataScopeName
     * @param [type] $dataSourceName
     */
    public function __construct(
        FinderLocator $locator,
        FinderProducts $finderProducts,
        UrlInterface $urlBuilder,
        $formName,
        $dataScopeName,
        $dataSourceName
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->locator = $locator;
        $this->finderProducts = $finderProducts;
        $this->formName = $formName;
        $this->dataScopeName = $dataScopeName;
        $this->dataSourceName = $dataSourceName;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $model = $this->locator->getFinder();
        $finderId = $model->getId();
        $data[$finderId]['finder-matrix'] = $this->finderProducts->getManualProductMatrix();

        return $data;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function modifyMeta(array $meta)
    {
        $meta = array_merge_recursive(
            $meta,
            [
                static::GROUP_FINDERPRODUCTS => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Add Products'),
                                'collapsible' => true,
                                'opened' => false,
                                'componentType' => Form\Fieldset::NAME,
                                'sortOrder' => 20,
                            ],
                        ],
                    ],
                    'children' => $this->getPanelChildrenData(),
                ],
            ]
        );

        return $meta;
    }

    /**
     * Prepares panel children products
     *
     * @return array
     */
    protected function getPanelChildrenData()
    {
        return [
            'finder_products_manual' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' =>  __('Manual Products'),
                            'formElement' => Container::NAME,
                            'componentType' => Container::NAME,
                            'template' => 'ui/form/components/complex',
                            'sortOrder' => 20,
                            'content' => __('Add products automatically from attributes which are mapped to respective dropdowns.'),
                        ],
                    ],
                ],
                'children' => [
                    'create_manual_products_button' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'formElement' => 'container',
                                    'componentType' => 'container',
                                    'component' => 'Magento_Ui/js/form/components/button',
                                    'actions' => [
                                        [
                                            'targetName' =>
                                                $this->dataScopeName . '.addProductModal',
                                            'actionName' => 'trigger',
                                            'params' => ['active', true],
                                        ],
                                        [
                                            'targetName' =>
                                                $this->dataScopeName . '.addProductModal',
                                            'actionName' => 'openModal',
                                        ],
                                    ],
                                    'title' => __('Add Products'),
                                    'sortOrder' => 20,
                                ],
                            ],
                        ],
                    ],
                ]
            ],
            'finder-matrix' => $this->getGrid(),
        ];
    }

    /**
     * Returns dynamic rows configuration
     *
     * @return array
     */
    protected function getGrid()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__field-wide',
                        'componentType' => DynamicRows::NAME,
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'label' => '',
                        'renderDefaultRecord' => false,
                        'template' => 'ui/dynamic-rows/templates/grid',
                        'component' => 'Webkul_PartFinder/js/components/dynamic-rows-finder',
                        'addButton' => false,
                        'isEmpty' => true,
                        'itemTemplate' => 'record',
                        'dataProviderFromGrid' => $this->associatedListingPrefix.static::FINDER_MANUAL_PRODUCT_LISTING,
                        'dataProviderChangeFromGrid' => 'change_product',
                        'dataProviderFromWizard' => 'variations',
                        'map' => [
                            'id' => 'entity_id',
                            'name' => 'name',
                            'sku' => 'sku',
                            'thumbnail_image' => 'thumbnail',
                            'status' => 'status',
                            'attributes' => 'attributes',
                        ],
                        'links' => [
                            'insertDataFromGrid' => '${$.provider}:${$.dataProviderFromGrid}',
                            'insertDataFromWizard' => '${$.provider}:${$.dataProviderFromWizard}',
                            'changeDataFromGrid' => '${$.provider}:${$.dataProviderChangeFromGrid}',
                        ],
                        'sortOrder' => 30,
                        'columnsHeader' => false,
                        'columnsHeaderAfterRender' => true,
                        'modalWithGrid' => 'ns=' . $this->formName . ', index='
                            . static::MANUAL_PRODUCT_MODAL,
                        'gridWithProducts' => 'ns=' . $this->associatedListingPrefix
                            . static::FINDER_MANUAL_PRODUCT_LISTING
                            . ', index=' . static::FINDER_MANUAL_PRODUCT_LISTING,
                    ],
                ],
            ],
            'children' => $this->getRows(),
        ];
    }

    /**
     * Returns Dynamic rows records configuration
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getRows()
    {
        return [
            'record' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => Container::NAME,
                            'isTemplate' => true,
                            'is_collection' => true,
                            'component' => 'Magento_Ui/js/dynamic-rows/record',
                            'dataScope' => '',
                        ],
                    ],
                ],
                'children' => [
                    'thumbnail_image_container' => $this->getColumn(
                        'thumbnail_image',
                        __('Image'),
                        [
                            'fit' => true,
                            'formElement' => 'fileUploader',
                            'componentType' => 'fileUploader',
                            'component' => 'Webkul_PartFinder/js/components/file-uploader',
                            'elementTmpl' => 'Webkul_PartFinder/components/file-uploader',
                            'fileInputName' => 'image',
                            'isMultipleFiles' => false,
                            'links' => [
                                'thumbnailUrl' => '${$.provider}:${$.parentScope}.thumbnail_image',
                                'thumbnail' => '${$.provider}:${$.parentScope}.thumbnail',
                                'smallImage' => '${$.provider}:${$.parentScope}.small_image',
                            ],
                            'uploaderConfig' => [
                                'url' => $this->urlBuilder->getUrl(
                                    'catalog/product_gallery/upload'
                                ),
                            ],
                            'dataScope' => 'image',
                        ],
                        [
                            'elementTmpl' => 'ui/dynamic-rows/cells/thumbnail',
                            'fit' => true,
                            'sortOrder' => 0
                        ]
                    ),
                    'name_container' => $this->getColumn(
                        'name',
                        __('Name'),
                        [],
                        ['dataScope' => 'name']
                    ),
                    'sku_container' => $this->getColumn(
                        'sku',
                        __('SKU'),
                        [
                            'validation' =>
                                [
                                    'required-entry' => true,
                                    'max_text_length' => Sku::SKU_MAX_LENGTH,
                                ]
                        ]
                    ),
                    'status' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType' => 'text',
                                    'component' => 'Magento_Ui/js/form/element/abstract',
                                    'template' => 'Magento_ConfigurableProduct/components/cell-status',
                                    'label' => __('Status'),
                                    'dataScope' => 'status',
                                ],
                            ],
                        ],
                    ],
                    'attributes' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'component' => 'Magento_Ui/js/form/element/text',
                                    'elementTmpl' => 'ui/dynamic-rows/cells/text',
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'label' => __('Dropdowns'),
                                ],
                            ],
                        ],
                    ],
                    'actionsList' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'additionalClasses' => 'data-grid-actions-cell',
                                    'componentType' => 'text',
                                    'component' => 'Magento_Ui/js/form/element/abstract',
                                    'template' => 'Webkul_PartFinder/components/actions-list',
                                    'label' => __('Actions'),
                                    'fit' => true,
                                    'dataScope' => 'status',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get configuration of column
     *
     * @param string $name
     * @param \Magento\Framework\Phrase $label
     * @param array $editConfig
     * @param array $textConfig
     * @return array
     */
    protected function getColumn(
        $name,
        \Magento\Framework\Phrase $label,
        $editConfig = [],
        $textConfig = []
    ) {
        $fieldEdit['arguments']['data']['config'] = [
            'dataType' => Form\Element\DataType\Number::NAME,
            'formElement' => Form\Element\Input::NAME,
            'componentType' => Form\Field::NAME,
            'dataScope' => $name,
            'fit' => true,
            'visibleIfCanEdit' => true,
            'imports' => [
                'visible' => '${$.provider}:${$.parentScope}.canEdit'
            ],
        ];
        $fieldText['arguments']['data']['config'] = [
            'componentType' => Form\Field::NAME,
            'formElement' => Form\Element\Input::NAME,
            'elementTmpl' => 'Magento_ConfigurableProduct/components/cell-html',
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => $name,
            'visibleIfCanEdit' => false,
            'imports' => [
                'visible' => '!${$.provider}:${$.parentScope}.canEdit'
            ],
        ];
        $fieldEdit['arguments']['data']['config'] = array_replace_recursive(
            $fieldEdit['arguments']['data']['config'],
            $editConfig
        );
        $fieldText['arguments']['data']['config'] = array_replace_recursive(
            $fieldText['arguments']['data']['config'],
            $textConfig
        );
        $container['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
            'label' => $label,
            'dataScope' => '',
        ];
        $container['children'] = [
            $name . '_edit' => $fieldEdit,
            $name . '_text' => $fieldText,
        ];

        return $container;
    }

     /**
      * Returns Buttons Set configuration
      *
      * @return array
      */
    protected function getButtons()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'component' => 'Magento_Ui/js/form/components/button',
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'label' => false,
                        'template' => 'ui/form/components/complex',
                        'createConfigurableButton' => 'ns = ${ $.ns }, index = create_configurable_products_button',
                    ],
                ],
            ],
            'children' => [
                'create_configurable_products_button' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'container',
                                'componentType' => 'container',
                                'component' => 'Magento_Ui/js/form/components/button',
                                'actions' => [
                                    [
                                        'targetName' =>
                                            $this->dataScopeName . '.addProductModal',
                                        'actionName' => 'trigger',
                                        'params' => ['active', true],
                                    ],
                                    [
                                        'targetName' =>
                                            $this->dataScopeName . '.addProductModal',
                                        'actionName' => 'openModal',
                                    ],
                                ],
                                'title' => __('Add Products'),
                                'sortOrder' => 20,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
