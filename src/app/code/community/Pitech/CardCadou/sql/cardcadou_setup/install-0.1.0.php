<?php

$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('cardcadou/transaction'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Id')
    ->addColumn('card_serie', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
    ), 'Card Serie')
    ->addColumn('amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
    ), 'Card Amount')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Order Id')
    ->addColumn('uuid', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
    ), 'Transaction UUID')
    ->addColumn('api_reference', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
    ), 'API Reference')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable' => false,
    ), 'Updated At')
    ->addColumn('order_status', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
    ), 'Order Status')
    ->addColumn('order_deny_code', Varien_Db_Ddl_Table::TYPE_TINYINT, 3, array(
        'nullable' => false,
        'default' => '0',
    ), 'Order Deny Code')
    ->addColumn('cancel_status', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
    ), 'Cancel Status')
    ->addColumn('cancel_deny_code', Varien_Db_Ddl_Table::TYPE_TINYINT, 3, array(
        'nullable' => false,
        'default' => '0',
    ), 'Cancel Deny Code')
    ->addColumn('confirmation_status', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
    ), 'Confirmation Status')
    ->addColumn('deny_code', Varien_Db_Ddl_Table::TYPE_TINYINT, 3, array(
        'nullable' => false,
        'default' => '0',
    ), 'Confirmation Deny Code');
$installer->getConnection()->createTable($table);

$installer->endSetup();