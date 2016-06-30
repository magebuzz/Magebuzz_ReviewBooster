<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Reviewbooster_Model_Resource_Reminder_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {
  protected function _construct() {
    $this->_init('reviewbooster/reminder');
  }

  public function sendRemindersToCustomers($force_sending = false, $reminder_ids = array()) {
    $this->addFieldToSelect('order_id');
    $this->addFieldToSelect('customer_email');
    $this->getSelect()->distinct('order_id');
	  $_before_filter_reminder_ids = $this->getAllIds();

    if (!$force_sending) {
      /* not remind customer who does not subscribe email letter */
      if (Mage::getStoreConfig('reviewbooster/reminder/check_newsletter_subscribe')) {
        $this->getSelect()->joinLeft(
          array('newsletter_sub_table' => Mage::getSingleton('core/resource')->getTableName('newsletter_subscriber')),
          'main_table.customer_email = newsletter_sub_table.subscriber_email',
          array('subscriber_status')
        );
        $this->addFieldToFilter('subscriber_status','1');
      }

      /* not remind customer who is in review booster unsubscribed list */
      $this->getSelect()->joinLeft(
        array('reviewbooster_unsub_table' => Mage::getSingleton('core/resource')->getTableName('magebuzz_reviewbooster_unsubscribed_customer')),
        'main_table.customer_email = reviewbooster_unsub_table.unsub_email',
        array('unsub_id')
      );
      $this->addFieldToFilter('unsub_id', array('null' => true));

      /* update status 'not_sent_by_unsubscribed' for reminders */
      $_after_filter_reminders = $this->getAllIds();
      $_reminder_ids_unsubscriber = array_diff($_before_filter_reminder_ids, $_after_filter_reminders);
      Mage::helper('reviewbooster')->updateReminderStatus($_reminder_ids_unsubscriber);
      /* end */

      $reminder_ids = $_after_filter_reminders;
    }
    foreach ($this->getItems() as $item) {
      Mage::helper('reviewbooster')->sendReminderByOrder($item->getOrderId(), $force_sending, $reminder_ids);
    }

  }
}