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
namespace Ekomi\ProductReviewContainer\Model;

use Ekomi\ProductReviewContainer\Helper\Data;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Bundle\Model\ResourceModel\Selection;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link;

/**
 * Class Reviews
 *
 * @package Ekomi\ProductReviewContainer\Model
 */
class Reviews extends AbstractModel
{
    public $_helper;
    public $_productLinks;
    public $_bundleSelection;

    /**
     * Reviews constructor.
     *
     * @param Selection       $bundleSelection
     * @param Link            $productLink
     * @param Data            $helper
     * @param Context         $context
     * @param Registry        $registry
     * @param null            $resource
     * @param AbstractDb|null $resourceCollection
     * @param array           $data
     */
    public function __construct( Selection $bundleSelection, Link $productLink,
        Data $helper, Context $context, Registry $registry,  $resource = null,
        AbstractDb $resourceCollection = null, array $data = [] )
    {
        $this->_helper = $helper;
        $this->_productLinks = $productLink;
        $this->_bundleSelection = $bundleSelection;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Ekomi\ProductReviewContainer\Model\Resource\Reviews');
    }

    /**
     * @param string $range
     *
     * @return array
     * @throws Exception
     */
    public function populateTable($range = '1w')
    {
        if(!$this->_helper->getIsActive()) {
            return array('status'  => 'error',
                         'message' => 'Plugin is disabled.');
        }

        $shopId     = $this->_helper->getShopId();
        $apiReviews = $this->getApiReviews($range);
        $apiReviews = is_array($apiReviews) ? $apiReviews : array();

        foreach ($apiReviews as $review) {

            if ($this->reviewExists($review))
                continue;

            $row = array(
                'shop_id'        => $shopId,
                'order_id'       => $review['order_id'],
                'product_id'     => $review['product_id'],
                'timestamp'      => $review['submitted'],
                'stars'          => $review['rating'],
                'review_comment' => $review['review'],
                'helpful'        => 0,
                'nothelpful'     => 0,
            );
            $this->setData($row);
            $this->save();
        }
    }

    /**
     * @param string $range
     *
     * @return array|bool|mixed|object
     */
    public function getApiReviews($range = '1w')
    {
        if(!$this->_helper->getIsActive())
            return false;

        $ekomi_api_url = 'http://api.ekomi.de/v3/getProductfeedback?interface_id=' .
            $this->_helper->getShopId() . '&interface_pw=' . $this->_helper->getShopPw() .
            '&type=json&charset=utf-8&range=' . $range;

        $product_reviews = file_get_contents($ekomi_api_url);

        return json_decode($product_reviews, true);
    }

    /**
     * @param $review
     *
     * @return bool
     */
    public function reviewExists( $review )
    {
        if(!$this->_helper->getIsActive())
            return false;

        $results = $this->getCollection()
            ->addFieldToFilter('shop_id',    $this->_helper->getShopId())
            ->addFieldToFilter('order_id',   $review['order_id'])
            ->addFieldToFilter('product_id', $review['product_id'])
            ->addFieldToFilter('timestamp',  $review['submitted'])
            ->addFieldToSelect('order_id');

        if( $results->getData() )
            return true;

        return false;
    }


    /**
     *  Get Child product Ids
     *
     * @param $product
     *
     * @return array|bool
     */
    protected function getChildProductIds($product)
    {
        if(!$this->_helper->getIsActive())
            return false;

        $productIDs = array();
        $productIDs[] = $product->getId();

        if (!$this->_helper->getGroupReview())
            return $productIDs;

        /**
         * for configurable products
         */
        if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $children = $product->getTypeInstance()->getUsedProducts($product);
            foreach($children as $child) {
                $productIDs[] = $child->getId();
            }
        }

        /**
         * for bundle products
         */
        if($product->getTypeId() == \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
            $children = $this->_bundleSelection->getChildrenIds($product->getId(), true);
            foreach($children as $childIds) {
                if(is_array($childIds)) {
                    foreach($childIds as $childId) {
                        $productIDs[] = $childId;
                    }
                } else {
                        $productIDs[] = $childIds;
                    }
            }
        }

        /**
         * for grouped products
         */
        if($product->getTypeId() == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {

            $children =  $this->_productLinks->getChildrenIds(
                $product->getId(), Link::LINK_TYPE_GROUPED
            );
            foreach($children as $childIds) {
                if(is_array($childIds)) {
                    foreach($childIds as $childId) {
                        $productIDs[] = $childId;
                    }
                } else {
                    $productIDs[] = $childIds;
                }
            }
        }

        return $productIDs;
    }

    /**
     * Fetches product reviews stars average
     *
     * @param type $productId Product Id
     *
     * @return double stars average
     */
    public function starsAverage($product)
    {
        if(!$this->_helper->getIsActive())
            return false;

        $productIDs = $this->getChildProductIds($product);

        $avg = $this->getCollection()
            ->addFieldToSelect('stars')
            ->addFieldToFilter('product_id', $productIDs)
            ->addFieldToFilter('shop_id', $this->_helper->getShopId());
        $avg->getSelect()
            ->columns('AVG(stars) as avgStars');
        $avg = $avg->getData();

        $starsAvg = 0;
        if (isset($avg[0]['avgStars'])) {
            $starsAvg = number_format($avg[0]['avgStars'], 1);
        }

        return $starsAvg;
    }

    /**
     * Gets Review stars count
     *
     * @param type $productId Product Id
     *
     * @return int Reviews Stars count
     */
    public function reviewStarsCount($product)
    {
        if(!$this->_helper->getIsActive())
            return false;

        $productIDs = $this->getChildProductIds($product->getId());

        $result = $this->getCollection()
            ->addFieldToSelect('id')
            ->addFieldToSelect('stars')
            ->addFieldToFilter('product_id', $productIDs)
            ->addFieldToFilter('shop_id', $this->_helper->getShopId());

        $result->getSelect()
            ->columns('count(id) as starsCount')
            ->group('stars');

        $result = $result->getData();

        $stars_array = array();
        foreach ($result as $key => $value) {
            $stars_array[$value['stars'] . 'stars'] = $value['starsCount'];
        }

        // set count for all stars
        for ($i = 1; $i <= 5; $i++) {
            if (!isset($stars_array[$i . 'stars'])) {
                $stars_array[$i . 'stars'] = 0;
            }
        }

        return $stars_array;
    }

    /**
     * Counts Product reviews
     *
     * @param string $productId Product Id
     *
     * @return int Product reviews count
     */
    public function productReviewsCount($product)
    {
        if(!$this->_helper->getIsActive())
            return false;

        $productIDs = $this->getChildProductIds($product->getId());

        $result = $this->getCollection()
            ->addFieldToSelect('stars')
            ->addFieldToFilter('product_id', $productIDs)
            ->addFieldToFilter('shop_id', $this->_helper->getShopId());
        $result->getSelect()
            ->columns('count(*) as reviewsCount');
        $result = $result->getData();

        $count = 0;
        if (isset($result[0]['reviewsCount'])) {
            $count = $result[0]['reviewsCount'];
        }
        return $count;
    }

    /**
     * Gets product reviews
     *
     * @param type $productId   Product Id
     * @param type $orderBy     Order By
     * @param type $sortType    Sort By ASC|DESC
     * @param type $offset_page Page offset
     * @param type $limit       Limit per page
     *
     * @return array Product Reviews
     */
    public function productReviews(
        $productId, $orderBy = 'timestamp', $sortType = 'DESC', $offset_page = '1', $limit = '5'
    )
    {
        if(!$this->_helper->getIsActive())
            return false;

        $productIDs = $this->getChildProductIds($productId);

        $result = $this->getCollection()
            ->addFieldToSelect('*')
            ->addFieldToFilter('product_id', $productIDs)
            ->addFieldToFilter('shop_id', $this->_helper->getShopId())
            ->setOrder($orderBy, $sortType)
            ->setCurPage($offset_page)
            ->setPageSize($limit);

        $result = $result->getData();

        return $result;
    }

    /**
     * Saves Review Feedback
     *
     * @param type $review_id   The review Id
     *
     * @param type $helpfulness 0 for Helpful 1 for not helpful
     *
     * @return boolean
     */
    public function rateReviewHelpfulness($review_id, $helpfulness)
    {
        if(!$this->_helper->getIsActive())
            return false;

        // sanitize data
        $helpfulness = trim($helpfulness);
        $review_id   = trim($review_id);

        // get the right column
        $column = $helpfulness == '1' ? 'helpful' : 'nothelpful';

        $tempReview = $this->getSingleReview($review_id);

        if ($tempReview) {
            $data = array($column => $tempReview[$column] + 1);

            $review = $this->load($review_id)->addData($data);

            try {
                $review->save();
                return true;
            } catch (Exception $exc) {

            }
        }
        return false;
    }

    /**
     * Gets single review
     *
     * @param type $review_id The review Id
     *
     * @return object Review object
     */
    public function getSingleReview($review_id)
    {
        if(!$this->_helper->getIsActive())
            return false;

        // sanitize data
        $review_id = trim($review_id);
        $review = $this->load($review_id)->getData();

        return $review;
    }
}
