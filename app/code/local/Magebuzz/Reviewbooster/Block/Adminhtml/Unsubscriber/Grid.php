<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/
class Magebuzz_Reviewbooster_Block_Adminhtml_Unsubscriber_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

  public function __construct()
  {
    parent::__construct();
    $this->setId('unsubscriberGrid');
    $this->setDefaultSort('unsub_id');
    $this->setDefaultDir('ASC');
    $this->setSaveParametersInSession(false);
    $this->setUseAjax(false);
    $this->setVarNameFilter('unsub_filter');
  }

  protected function _getStore()
  {
    $storeId = (int) $this->getRequest()->getParam('store', 0);
    return Mage::app()->getStore($storeId);
  }

  protected function _prepareCollection()
  {
    $collection = Mage::getModel('reviewbooster/unsubscriber')->getCollection();
    $this->setCollection($collection);
    parent::_prepareCollection();
    return $this;
  }


  protected function _prepareColumns()
  {
    $this->addColumn('unsub_id',
      array(
        'header'=> Mage::helper('reviewbooster')->__('Unsubscribed ID'),
        // 'width' => '50px',
        'type'  => 'number',
        'index' => 'unsub_id',
    ));


    $this->addColumn('unsub_email',
      array(
        'header'=> Mage::helper('reviewbooster')->__('Unsubscribed Email'),
        'type' => 'text',
        'index' => 'unsub_email',
    ));

    $this->addColumn('created_at', array(
      'header' => Mage::helper('reviewbooster')->__('Created At'),
      'index' => 'created_at',
      'type' => 'datetime',
      // 'width' => '100px',
    ));
    return parent::_prepareColumns();
  }

  protected function _prepareMassaction()
  {
    $this->setMassactionIdField('unsub_id');
    $this->getMassactionBlock()->setFormFieldName('unsubscriber');
    $this->getMassactionBlock()->addItem('delete', array(
       'label'=> Mage::helper('reviewbooster')->__('Delete'),
       'url'  => $this->getUrl('*/*/massDeleteUnsubscriber'),
       'confirm' => Mage::helper('reviewbooster')->__('Are you sure to Delete all Checked unsubscriber(s) ?')
    ));
  }
}
