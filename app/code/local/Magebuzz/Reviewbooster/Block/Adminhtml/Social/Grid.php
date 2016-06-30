<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/
class Magebuzz_Reviewbooster_Block_Adminhtml_Social_Grid extends Mage_Adminhtml_Block_Review_Grid
{

  protected function _prepareColumns() {

    parent::_prepareColumns();
    $this->removeColumn('sku');
    $this->removeColumn('action');

    $this->addColumn('social_sharing',
      array(
        'header'    => Mage::helper('reviewbooster')->__('Social Sharing'),
        'width'     => '100px',
        'renderer'  => 'reviewbooster/adminhtml_social_render_sharing',
        'filter'    => false,
        'sortable'  => false,
    ));

  }

  public function getRowUrl($row)
  {
    return "";
  }

  protected function _prepareMassaction() {
    return;
  }
}
