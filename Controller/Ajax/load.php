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
namespace Ekomi\ProductReviewContainer\Controller\Ajax;

/**
 * Class Load
 *
 * @package Ekomi\ProductReviewContainer\Controller\Ajax
 */
class Load extends \Magento\Framework\App\Action\Action
{
    protected $_layoutFactory;
    protected $_reviewsModel;
    protected $_jsonHelper;
    protected $_helper;

    /**
     * Load constructor.
     *
     * @param \Magento\Framework\App\Action\Context              $context
     * @param \Magento\Framework\Json\Helper\Data                $jsonHelper
     * @param \Ekomi\ProductReviewContainer\Helper\Data          $helper
     * @param \Magento\Framework\View\LayoutFactory              $layoutFactory
     * @param \Ekomi\ProductReviewContainer\Model\Reviews        $reviewsModel
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Ekomi\ProductReviewContainer\Helper\Data $helper,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Ekomi\ProductReviewContainer\Model\Reviews $reviewsModel
    ) {
        parent::__construct($context);
        $this->_helper = $helper;
        $this->_jsonHelper = $jsonHelper;
        $this->_reviewsModel = $reviewsModel;
        $this->_layoutFactory = $layoutFactory;
    }

    /**
     * @return bool
     */
    public function execute()
    {
        if(!$this->getRequest()->isAjax()) {
            return false;
        }

        $product_id  = $this->getRequest()->getPost('product_id');
        $product_id  = isset($product_id) ? $product_id : null;
        $limit       = $this->getRequest()->getPost('limit');
        $limit       = isset($limit) ? $limit : 10;
        $offset_page = $this->getRequest()->getPost('offset_page');
        $offset_page = isset($offset_page) ? $offset_page : 0;
        $filter_type = $this->getRequest()->getPost('filter_type');
        $filter_type = isset($filter_type) ? $filter_type : 0;

        // Check submited data
        if (is_null($product_id)) {
            $response = array(
                'state'   => 'error',
                'message' => __('Please provide the review parameters'),
                '_POST'   => $this->getRequest()->getPost(),
            );
        } else {
            // resolve filter type
            $orderBy  = $this->_helper->resolveOrderBy($filter_type);
            $sortType = $this->_helper->resolveSortType($filter_type);
            $reviews  = $this->_reviewsModel->productReviews($product_id, $orderBy, $sortType, $offset_page, $limit);

            $reviewHtml = $this->_layoutFactory->create()
                ->createBlock('Ekomi\ProductReviewContainer\Block\Widget\Reviews')
                ->setTemplate('Ekomi_ProductReviewContainer::reviewscontainerpartial.phtml')
                ->setData('reviews', $reviews)
                ->toHtml();

            $response = array(
                'state' => 'success',
                'message' => __('reviews loaded!'),
                'reviews_data' => ['result' => $reviewHtml, 'count' => count($reviews)],
                '_POST' => $this->getRequest()->getPost()
            );
        }
        $this->getResponse()->setBody($this->_jsonHelper->jsonEncode($response));
    }
}