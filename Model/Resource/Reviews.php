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
namespace Ekomi\ProductReviewContainer\Model\Resource;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Reviews
 *
 * @package Ekomi\ProductReviewContainer\Model\Resource
 */
class Reviews extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('prc_product_reviews', 'id');
    }
}