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
namespace Ekomi\ProductReviewContainer\Block\Widget;

use Magento\Framework\Registry;

/**
 * Class Reviews
 *
 * @package Ekomi\ProductReviewContainer\Block\Widget
 */
class Reviews extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    protected $_registry;
    protected $_product;
    protected $_helper;
    protected $_reviewModel;
    public $limit = 5;

    /**
     * Reviews constructor.
     *
     * @param Registry                                         $registry
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Ekomi\ProductReviewContainer\Model\Reviews      $reviewModel
     * @param \Ekomi\ProductReviewContainer\Helper\Data        $helper
     * @param array                                            $data
     */
    public function __construct(
        Registry $registry,
        \Magento\Framework\View\Element\Template\Context $context,
        \Ekomi\ProductReviewContainer\Model\Reviews $reviewModel,
        \Ekomi\ProductReviewContainer\Helper\Data $helper,
        array $data = []
    ) {
        $this->_helper      = $helper;
        $this->_registry    = $registry;
        $this->_reviewModel = $reviewModel;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getCurrentProduct()
    {
        return $this->_registry->registry('product');
    }

    /**
     * Gets Stars Average
     *
     * @return double Stars average
     */
    public function getMiniStarsAvg()
    {
        return $this->_reviewModel->starsAverage($this->getCurrentProduct());
    }

    /**
     * Counts products reviews
     *
     * @return int Product review count
     */
    public function getProductReviewsCount()
    {
        return $this->_reviewModel->productReviewsCount($this->getCurrentProduct());
    }

    /**
     * Counts Stars
     *
     * @return int Reviews stars count
     */
    public function getStarsCount()
    {
        return $this->_reviewModel->reviewStarsCount($this->getCurrentProduct());
    }

    /**
     * Gets product reviews
     *
     * @return array product reviews
     */
    public function getProductReviews()
    {
        $orderBy  = 'timestamp';
        $sortType = 'DESC';
        $offset   = '1';

        return $this->_reviewModel->productReviews(
            $this->getCurrentProduct()->getId(),
            $orderBy,
            $sortType,
            $offset,
            $this->limit
        );
    }

    /**
     * Gets product name
     *
     * @return string product name
     */
    public function getProductName()
    {
        $productName = $this->getCurrentProduct()->getName();
        return $productName ? $productName : 'Current Product';
    }

    /**
     * Gets product name
     *
     * @return string product name
     */
    public function getNoReviewMessage()
    {
        return $this->_helper->getNoReviewMessage();
    }

    /**
     * Checks is Module enabled
     *
     * @return boolean True if enabled False otherwise
     */
    public function isModuleEnabled()
    {
        return $this->_helper->getIsActive();
    }

    /**
     * Returns path to JS directory
     *
     * @return string
     */
    public function getJsPath()
    {
        return $this->getViewFileUrl('Ekomi_ProductReviewContainer::js');
    }
}
