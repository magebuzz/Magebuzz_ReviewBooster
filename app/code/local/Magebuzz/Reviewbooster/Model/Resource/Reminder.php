<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Reviewbooster_Model_Resource_Reminder extends Mage_Core_Model_Resource_Db_Abstract {
	
	protected function _construct()
  {
    $this->_init('reviewbooster/reminder', 'reminder_id');
  }
}