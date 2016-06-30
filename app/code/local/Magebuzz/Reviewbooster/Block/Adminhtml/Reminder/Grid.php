<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/
class Magebuzz_Reviewbooster_Block_Adminhtml_Reminder_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

  public function __construct()
  {
    parent::__construct();
    $this->setId('reminderGrid');
    $this->setDefaultSort('reminder_id');
    $this->setDefaultDir('DESC');
    $this->setSaveParametersInSession(true);
    $this->setUseAjax(false);
    $this->setVarNameFilter('reminder_filter');

  }

  protected function _getStore()
  {
    $storeId = (int) $this->getRequest()->getParam('store', 0);
    return Mage::app()->getStore($storeId);
  }

  protected function _prepareCollection()
  {
    $collection = Mage::getModel('reviewbooster/reminder')->getCollection();
		$collection->getSelect()->joinLeft(
			array("t1" => Mage::getSingleton('core/resource')->getTableName('sales_flat_order')), 
			"main_table.order_id = t1.entity_id", 
			array("increment_id" => "t1.increment_id"));
    $this->setCollection($collection);
    parent::_prepareCollection();
    return $this;
  }

  protected function _prepareColumns()
  {
    $this->addColumn('reminder_id',
      array(
        'header'=> Mage::helper('reviewbooster')->__('Reminder ID'),
        // 'width' => '50px',
        'type'  => 'number',
        'index' => 'reminder_id',
    ));

    if (!Mage::app()->isSingleStoreMode()) {
      $this->addColumn('store_id', array(
        'header'    => Mage::helper('reviewbooster')->__('From (Store)'),
        'index'     => 'store_id',
        'type'      => 'store',
        'store_view'=> true,
        // 'display_deleted' => true,
      ));
    }


	$this->addColumn('order_id',
      array(
        'header'=> Mage::helper('reviewbooster')->__('Order'),
        'type' => 'text',
        'index' => 'order_id',
				'renderer'  => 'reviewbooster/adminhtml_reminder_render_vieworder',
    ));


    $this->addColumn('product_id',
      array(
        'header'=> Mage::helper('reviewbooster')->__('Product'),
        'type'  => 'action',
        'width' => '100px',
        'getter' => 'getProductId',
        'actions'  => array(
          array(
            'url'     => array('base'=>'*/catalog_product/edit'),
            'caption' => $this->helper('reviewbooster')->__('View product'),
            'field' => 'id',
          ),
        )
    ));

    $this->addColumn('product_name',
      array(
        'header'=> Mage::helper('reviewbooster')->__('Product Name'),
        'type' => 'text',
        'index' => 'product_name',
    ));


    $this->addColumn('customer_email',
      array(
        'header'=> Mage::helper('reviewbooster')->__('Customer Email'),
        'type' => 'text',
        'index' => 'customer_email',
    ));

    $this->addColumn('created_at', array(
      'header' => Mage::helper('reviewbooster')->__('Created At'),
      'index' => 'created_at',
      'type' => 'datetime',
      // 'width' => '100px',
    ));

    $this->addColumn('sending_at', array(
      'header' => Mage::helper('reviewbooster')->__('Sending At'),
      'index' => 'sending_at',
      'type' => 'datetime',
      // 'width' => '100px',
    ));

    $this->addColumn('status',
      array(
        'header'=> Mage::helper('reviewbooster')->__('Reminder Status'),
        'type' => 'options',
        'index' => 'reminder_status',
        'options' => array(
          'pending' => 'pending',
          'sent' => 'sent',
          'not_sent' => 'not sent',
          'not_sent_by_unsubscribed' => 'not send by unsubscribed',
          'replied' => 'replied'
        )
    ));


    return parent::_prepareColumns();
  }

  protected function _prepareMassaction()
  {
    $this->setMassactionIdField('reminder_id');
    $this->getMassactionBlock()->setFormFieldName('reminder');

    $this->getMassactionBlock()->addItem('delete', array(
       'label'=> Mage::helper('reviewbooster')->__('Delete'),
       'url'  => $this->getUrl('*/*/massDelete'),
       'confirm' => Mage::helper('reviewbooster')->__('Are you sure you want to Delete all Checked reminder(s) ?')
    ));

    $this->getMassactionBlock()->addItem('send', array(
       'label'=> Mage::helper('reviewbooster')->__('Send'),
       'url'  => $this->getUrl('*/*/massSend'),
       'confirm' => Mage::helper('reviewbooster')->__('Are you sure you want to Send all Checked reminder(s) ?')
    ));
  }
}
