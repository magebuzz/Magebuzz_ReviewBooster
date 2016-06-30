<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Reviewbooster_Model_Unsubscriber extends Mage_Core_Model_Abstract {
  
  protected function _construct() {
    $this->_init('reviewbooster/unsubscriber');
  }

  protected function _beforeSave() {
    if ($this->isObjectNew()) {
      $now = Mage::getSingleton('core/date')->timestamp(time());
      $this->setCreatedAt(date('Y-m-d h:i:s', $now));
    }
    return parent::_beforeSave();
  }
}