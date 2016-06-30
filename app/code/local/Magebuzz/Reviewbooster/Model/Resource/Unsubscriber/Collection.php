<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Reviewbooster_Model_Resource_Unsubscriber_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract{

	protected function _construct() {
    $this->_init('reviewbooster/unsubscriber');
  }
}