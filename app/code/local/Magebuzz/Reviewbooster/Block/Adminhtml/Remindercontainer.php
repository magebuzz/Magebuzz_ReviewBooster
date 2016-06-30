<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/
class Magebuzz_Reviewbooster_Block_Adminhtml_Remindercontainer extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()  {
    $this->_controller = 'adminhtml_reminder';
    $this->_blockGroup = 'reviewbooster';
    $this->_headerText = 'Manage Reminder';

	  	$data = array(
		  'label' => 'Test cronjob',
		  'onclick' => "setLocation('{$this->getUrl('*/*/sendReminder')}')",
	  );
	  Mage_Adminhtml_Block_Widget_Container::addButton('test', $data, 0, 100,  'header', 'header');

    parent::__construct();
    $this->removeButton('add');
  }
}