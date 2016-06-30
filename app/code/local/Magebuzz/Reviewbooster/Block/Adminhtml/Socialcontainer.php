<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/
class Magebuzz_Reviewbooster_Block_Adminhtml_Socialcontainer extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()  {
    $this->_controller = 'adminhtml_social';
    $this->_blockGroup = 'reviewbooster';
    $this->_headerText = 'Social Sharing';
    parent::__construct();
    $this->removeButton('add');
    /* $this->_addButton('get_page_access_token', array(
      'label'     => Mage::helper('reviewbooster')->__('Connect to Facebook Fanpage'),
      'onclick'   => 'setLocation(\'' . $this->getUrl('*//*/getPageAccessToken', array('_secure' => true)) .'\')',
      // 'class'     => 'add', */
    //));
  }
}