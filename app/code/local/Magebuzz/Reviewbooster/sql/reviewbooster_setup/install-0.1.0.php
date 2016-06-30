<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

$installer = $this;
$installer->startSetup();

$table1 = $installer->getConnection()
  ->newTable($installer->getTable('magebuzz_reviewbooster_reminder'))
  ->addColumn('reminder_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
    ), 'Test User Id')
  ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    ), 'Store ID')
  ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    ), 'Order ID')
  ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    ), 'Product ID')
  ->addColumn('product_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
    ), 'Product Name')
  ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    ), 'Created at')
  ->addColumn('sending_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    ), 'Sending at')
  ->addColumn('customer_email', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
    ), 'Customer Email')
  ->addColumn('reminder_status', Varien_Db_Ddl_Table::TYPE_TEXT, 127, array(
    ), 'Reminder Status')
  ->addColumn('reminder_code', Varien_Db_Ddl_Table::TYPE_TEXT, 127, array(
      'key' => true,
    ), 'Reminder Code');
$installer->getConnection()->createTable($table1);

$table2 = $installer->getConnection()
  ->newTable($installer->getTable('magebuzz_reviewbooster_unsubscribed_customer'))
  ->addColumn('unsub_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
    ), 'Unsubscribed ID')
  ->addColumn('unsub_email', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
    'unique' => true,
    'nullable' => false,
    ), 'Unsubscribed Email')
  ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    ), 'Created at');
$installer->getConnection()->createTable($table2);

// $installer->getConnection()->addColumn($this->getTable('review_detail'), 'customer_email', 'varchar(255) NULL');
$installer->getConnection()->addColumn($this->getTable('review'), 'like_number', 'int DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('review'), 'dislike_number', 'int DEFAULT 0');

$installer->endSetup();

