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
 * Class Save
 *
 * @package Ekomi\ProductReviewContainer\Controller\Ajax
 */
class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Ekomi\ProductReviewContainer\Model\ReviewsFactory
     */
    protected $_reviewsModel;
    protected $_jsonHelper;

    /**
     * Save constructor.
     *
     * @param \Magento\Framework\App\Action\Context       $context
     * @param \Magento\Framework\Json\Helper\Data         $jsonHelper
     * @param \Ekomi\ProductReviewContainer\Model\Reviews $reviewsModel
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Ekomi\ProductReviewContainer\Model\Reviews $reviewsModel
    ) {
        parent::__construct($context);
        $this->_jsonHelper = $jsonHelper;
        $this->_reviewsModel = $reviewsModel;
    }

    /**
     * @return bool
     */
    public function execute()
    {
        if(!$this->getRequest()->isAjax()) {
            return false;
        }

        $review_id   = $this->getRequest()->getPost('review_id');
        $helpfulness = $this->getRequest()->getPost('helpfulness');

        if (!$review_id || is_null($helpfulness)) {
            $response = array(
                'state'       => 'error',
                'message'     => __('Please provide the review parameters'),
                '_POST'       => $this->getRequest()->getPost(),
                'helpfulness' => $helpfulness . ' ' . gettype($helpfulness)
            );
        } else {
            $rate_helpfulness = $this->_reviewsModel->rateReviewHelpfulness($review_id, $helpfulness);
            if ($rate_helpfulness) {
                $review   = $this->_reviewsModel->getSingleReview($review_id);
                $message  = ($review['helpful']) . ' out of '
                    . ($review['helpful'] + $review['nothelpful'])
                    . ' people found this review helpful';
                $response = array(
                    'state'            => 'success',
                    'message'          => __($message),
                    '_POST'            => $this->getRequest()->getPost(),
                    'rate_helpfulness' => $helpfulness == '1' ? 'helpful' : 'nothelpful'
                );
            } else {
                $response = array(
                    'state'            => 'error',
                    'message'          => __('Could not process the request! ' . $rate_helpfulness['last_error']),
                    '_POST'            => $this->getRequest()->getPost(),
                    'rate_helpfulness' => $rate_helpfulness,);
            }
        }

        $this->getResponse()->setBody(
            $this->_jsonHelper->jsonEncode($response)
        );
    }
}