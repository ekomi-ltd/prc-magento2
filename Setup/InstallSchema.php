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
namespace Ekomi\ProductReviewContainer\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class InstallSchema
 *
 * @package Ekomi\ProductReviewContainer\Setup
 */
class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     */
    public function install(
        \Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    )
    {
        $installer = $setup;
        $installer->startSetup();

        $tableName = $installer->getTable('prc_product_reviews');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'  => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'shop_id',
                    Table::TYPE_INTEGER,
                    9,
                    ['nullable' => false],
                    'Shop ID'
                )
                ->addColumn(
                    'order_id',
                    Table::TYPE_TEXT,
                    64,
                    ['nullable' => false],
                    'Order ID'
                )
                ->addColumn(
                    'product_id',
                    Table::TYPE_TEXT,
                    64,
                    ['nullable' => false],
                    'Product ID'
                )
                ->addColumn(
                    'timestamp',
                    Table::TYPE_INTEGER,
                    11,
                    ['nullable' => false],
                    'Timestamp'
                )
                ->addColumn(
                    'stars',
                    Table::TYPE_INTEGER,
                    1,
                    ['unsigned' => true, 'nullable' => false],
                    'Stars'
                )
                ->addColumn(
                    'review_comment',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Review Comment'
                )
                ->addColumn(
                    'helpful',
                    Table::TYPE_SMALLINT,
                    5,
                    ['nullable' => false, 'default' => '0'],
                    'Helpful'
                )
                ->addColumn(
                    'nothelpful',
                    Table::TYPE_SMALLINT,
                    5,
                    ['nullable' => false, 'default' => '0'],
                    'Not Helpful'
                )
                ->setComment('eKomi Product Review Container Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}