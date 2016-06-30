<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/
class Magebuzz_Reviewbooster_Block_Adminhtml_Unsubscribercontainer extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()  {
    //where is the controller
    $this->_controller = 'adminhtml_unsubscriber';
    $this->_blockGroup = 'reviewbooster';
    //text in the admin header
    $this->_headerText = 'Manage Unsubscribed Customer';
    parent::__construct();
    $this->removeButton('add');
  }
}