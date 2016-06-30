<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Reviewbooster_Helper_Data extends Mage_Core_Helper_Abstract {	
	/*
	* send reminder by order
	*/
  public function sendReminderByOrder($order_id, $force_sending = false, $reminder_ids = array(), $email_address = '') {
    $order = Mage::getModel('sales/order')->load($order_id);
    $customer_email = $order->getCustomerEmail();

    /* set testing email address*/
    if ($email_address != '') {
      $customer_email = $email_address;
    }

    $collection = Mage::getModel('reviewbooster/reminder')->getCollection();
    $collection->addFieldToSelect('*')
      ->addFieldToFilter('order_id', $order_id);

    /* limit collection in a reminder list */
    if (count($reminder_ids)) {
      $collection->addFieldToFilter('reminder_id', array('in', $reminder_ids));
    }

    /* send reminders regardless the statuses of reminders */
    if (!$force_sending) {
      $collection->addFieldToFilter('reminder_status', array('pending'))
      ->addFieldToFilter('sending_at', array(
        'lteq' => date('Y-m-d h:i:s', Mage::getSingleton('core/date')->timestamp(time())),
        'datetime' => true
      ));
    }

    $unsubscribeUrl = Mage::getUrl('reviewbooster/index/unsubscribe',array('code' => $collection->getFirstItem()->getReminderCode()));

    if ($collection->getSize() <= 0) return FALSE;
    /* send email to remind customers */
    try {
      $mailTemplate = Mage::getModel('core/email_template');
      $mailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $order->getStoreId()))
        ->sendTransactional(
          $templateId = Mage::getStoreConfig('reviewbooster/reminder/email_template', $order->getStoreId()),
          $sender = Mage::getStoreConfig('reviewbooster/reminder/email_sender', $order->getStoreId()),
          $customer_email,
          null,
          array('reminder_collection' => $collection, 'order' => $order, 'unsubscribeUrl' => $unsubscribeUrl),
          $order->getStoreId()
        );
      /* update reminders' statuses */
      if ($mailTemplate->getSentSuccess()) {
        foreach ($collection->getItems() as $reminder) {
          $reminder->setReminderStatus('sent');
          $reminder->save();
        }
      } else {
        foreach ($collection->getItems() as $reminder) {
          $reminder->setReminderStatus('not_sent');
          $reminder->save();
        }
      }
    } catch (Exception $e) {
//      Zend_Debug::dump($e->getMessage());
	    foreach ($collection->getItems() as $reminder) {
		    $reminder->setReminderStatus('not_sent');
		    $reminder->save();
	    }
      Mage::log($e->getMessage(), null, 'reviewbooster.log');
    }
    return TRUE;
  }

  /* if in time, the reminder not send, it will be change status to not send by unsubscribed */
  /* this function should be executed after sending reminders */
  public function updateReminderStatus($reminder_ids = array()) {
	  foreach ($reminder_ids as $reminder_id) {
      $reminder = Mage::getModel('reviewbooster/reminder')->load($reminder_id);
      $reminder->setData('reminder_status', 'not_sent_by_unsubscribed');
      $reminder->save();
	  }
  }
	
	public function getRecaptchaPublicKey() {
		return Mage::getStoreConfig('reviewbooster/product_page/public_key', Mage::app()->getStore()->getStoreId());		
	}

	public function getSecretKey() {
		return Mage::getStoreConfig('reviewbooster/product_page/private_key', Mage::app()->getStore()->getStoreId());		
	}
	
  public function postCaptchaData($response = '') {
		$url = 'https://www.google.com/recaptcha/api/siteverify';
		
		$data = array(
			'secret' => $this->getSecretKey(), 
			'response' => $response, 
			'remoteip' => $_SERVER['REMOTE_ADDR']
		);
		
		$dataString = 'secret='.$this->getSecretKey().'&response='.$response.'&remoteip='.$_SERVER['REMOTE_ADDR'];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 

		$result = curl_exec($ch);
		return json_decode($result);
	}

  public function isAutoApprovedConfig() {
		$storeId = Mage::app()->getStore()->getId();
    return Mage::getStoreConfig('reviewbooster/product_page/auto_approved', $storeId);
  }
	
	public function isCaptchaEnabled() {
		return (bool) Mage::getStoreConfig('reviewbooster/product_page/captcha_enabled', Mage::app()->getStore()->getId());
	}
	
	public function showCaptchaForLoggedInCustomer() {
		return (bool) Mage::getStoreConfig('reviewbooster/product_page/captcha_logged_in', Mage::app()->getStore()->getId());
	}
	
	public function canShowCaptcha() {
		if ($this->isCaptchaEnabled()) {
			if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
				return true;
			} 
			else if ($this->showCaptchaForLoggedInCustomer()) {
				return true;
			}
		}
		return false;
	}
	
	public function enableSocialSharing() {
		return (bool) Mage::getStoreConfig('reviewbooster/product_page/review_social_sharing_enabled', Mage::app()->getStore()->getId());
	}
	
	public function enableAdminNotification() {
		return (bool) Mage::getStoreConfig('reviewbooster/admin_notifier/enabled', Mage::app()->getStore()->getId());
	}
	
	public function isAutoReminderEnabled() {
		return (bool) Mage::getStoreConfig('reviewbooster/reminder/enabled', Mage::app()->getStore()->getId());
	}
	
	public function isOnlySendToNewsletterSubscriber() {
		return (bool) Mage::getStoreConfig('reviewbooster/reminder/check_newsletter_subscribe', Mage::app()->getStore()->getId());
	}
	
	public function isNotSendToReviewedCustomer() {
		return (bool) Mage::getStoreConfig('reviewbooster/reminder/not_remind_if_has_reviewed', Mage::app()->getStore()->getId());
	}
}