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
namespace Webkul\PartFinder\Model;

use Magento\Search\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\StringUtils as StdlibString;

class FinderQueryFactory extends \Magento\Search\Model\QueryFactory
{
    /**
     * Query variable
     */
    const QUERY_VAR_NAME = 'finder';
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var Query
     */
    private $query;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var StdlibString
     */
    private $string;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Data
     */
    private $queryHelper;

    /**
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StdlibString $string
     * @param Data|null $queryHelper
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StdlibString $string,
        Data $queryHelper = null
    ) {
        $this->request = $context->getRequest();
        $this->objectManager = $objectManager;
        $this->string = $string;
        $this->scopeConfig = $context->getScopeConfig();
        $this->queryHelper = $queryHelper === null ? $this->objectManager->get(Data::class) : $queryHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        if (!$this->query) {
            $maxQueryLength = $this->queryHelper->getMaxQueryLength();
            $minQueryLength = $this->queryHelper->getMinQueryLength();
            $rawQueryText = $this->getRawQueryText();
            $preparedQueryText = $rawQueryText;
            $query = $this->create()->loadByQueryText($preparedQueryText);
            if (!$query->getId()) {
                $query->setQueryText($preparedQueryText);
            }
            $query->setIsQueryTextExceeded(false);
            $query->setIsQueryTextShort(false);
            if ($this->request->getParam(self::QUERY_VAR_NAME)) {
                $query->setQueryParam(self::QUERY_VAR_NAME);
            }
            $this->query = $query;
        }
        return $this->query;
    }

    /**
     * Create new instance
     *
     * @param array $data
     * @return Query
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create(FinderQuery::class, $data);
    }

    /**
     * Retrieve search query text
     *
     * @return string
     */
    private function getRawQueryText()
    {
        $queryText = $this->request->getParam(self::QUERY_VAR_NAME);
        return ($queryText === null || is_array($queryText))
            ? ''
            : $this->string->cleanString(trim($queryText));
    }

    /**
     * @param string $queryText
     * @param int|string $maxQueryLength
     * @return bool
     */
    private function isQueryTooLong($queryText, $maxQueryLength)
    {
        return false;
    }

    /**
     * @param string $queryText
     * @param int|string $minQueryLength
     * @return bool
     */
    private function isQueryTooShort($queryText, $minQueryLength)
    {
        return false;
    }
}
