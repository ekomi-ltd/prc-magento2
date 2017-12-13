<?php
/**
 * Ekomi
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 */
namespace Ekomi\ProductReviewContainer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Data
 * @package Gielberkers\Example\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    const XML_PATH_ACTIVE        = 'prc/general/active';
    const XML_PATH_SHOP_ID       = 'prc/general/shop_id';
    const XML_PATH_SHOP_PASSWORD = 'prc/general/shop_password';
    const XML_PATH_GROUP_REVIEWS = 'prc/general/group_reviews';
    const XML_PATH_NO_REVIEW_MSG = 'prc/general/no_review_message';

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return mixed
     */
    public function getIsActive()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(
            self::XML_PATH_ACTIVE, $storeScope
        );
    }

    /**
     * @return mixed
     */
    public function getShopId($storeId = false)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        if($storeId){
            $shopId = $this->scopeConfig->getValue(
                self::XML_PATH_SHOP_ID,
                $storeScope,
                $storeId
            );
        } else {
            $shopId = $this->scopeConfig->getValue(
                self::XML_PATH_SHOP_ID, $storeScope
            );
        }

        return $shopId;
    }

    /**
     * @return mixed
     */
    public function getShopPw($storeId = false)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        if($storeId){
            $shopPw = $this->scopeConfig->getValue(
                self::XML_PATH_SHOP_PASSWORD,
                $storeScope,
                $storeId
            );
        } else {
            $shopPw = $this->scopeConfig->getValue(
                self::XML_PATH_SHOP_PASSWORD, $storeScope
            );
        }

        return $shopPw;
    }

    /**
     * @return mixed
     */
    public function getGroupReview()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(
            self::XML_PATH_GROUP_REVIEWS, $storeScope
        );
    }

    /**
     * @return mixed
     */
    public function getNoReviewMessage()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(
            self::XML_PATH_NO_REVIEW_MSG, $storeScope
        );
    }

    /**
     * Resolves Order By
     *
     * @param int $filter_type The sorting filter value
     *
     * @return string The Sorting filter
     */
    public function resolveOrderBy($filter_type)
    {
        $orderBy = '';
        switch ($filter_type) {
            case 1:
            case 2:
                $orderBy = 'timestamp';
                break;
            case 3:
                $orderBy = 'helpful';
                break;
            case 4:
            case 5:
                $orderBy = 'stars';
                break;
            default:
                $orderBy = 'timestamp';
                break;
        }
        return $orderBy;
    }

    /**
     * Resolves  Sort Type
     *
     * @param int $filter_type The sorting filter value
     *
     * @return string The Sorting filter
     */
    public function resolveSortType($filter_type)
    {
        $sortType = '';
        switch ($filter_type) {
            case 0:
            case 1:
            case 3:
            case 4:
                $sortType = 'DESC';
                break;
            case 2:
            case 5:
                $sortType = 'ASC';
                break;

            default:
                $sortType = 'ASC';
                break;
        }
        return $sortType;
    }
}