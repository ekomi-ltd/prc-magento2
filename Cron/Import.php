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
namespace Ekomi\ProductReviewContainer\Cron;

/**
 * Class Import
 *
 * @package Ekomi\ProductReviewContainer\Cron
 */
class Import
{
    protected $reviewsModel;

    /**
     * Import constructor.
     *
     * @param \Ekomi\ProductReviewContainer\Model\Reviews $reviews
     */
    public function __construct(
        \Ekomi\ProductReviewContainer\Model\Reviews $reviews
    ) {
        $this->reviewsModel = $reviews;
    }

    /**
     * Fetches reviews daily
     */
    public function execute()
    {
        $this->reviewsModel->populateTable('1w');
    }
}