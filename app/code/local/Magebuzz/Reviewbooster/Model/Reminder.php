<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Reviewbooster_Model_Reminder extends Mage_Core_Model_Abstract {
  protected function _construct() {
    $this->_init('reviewbooster/reminder');
  }

  protected function _getRemindDuration() {
    $days = Mage::getStoreConfig('reviewbooster/reminder/waiting_time');
    return $days*24*60*60;
    // return 60;
  }

  protected function _beforeSave() {
    if ($this->isObjectNew()) {
      $this->setReminderStatus('pending');
      $now = Mage::getSingleton('core/date')->timestamp(time());
      $this->setCreatedAt(date('Y-m-d h:i:s', $now));
      $this->setSendingAt(date('Y-m-d h:i:s', $now + $this->_getRemindDuration()));
      $this->setReminderCode(md5($this->getOrderId().$this->getProductId().$this->getCreatedAt()));
    }
    return parent::_beforeSave();
  }

}