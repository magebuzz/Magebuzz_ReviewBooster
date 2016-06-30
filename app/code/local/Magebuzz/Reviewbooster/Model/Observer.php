<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Reviewbooster_Model_Observer {
  // sending admin notification email when having a new review
  public function sendAdminEmail($observer) {
    $review = $observer->getEvent()->getObject();
    if ($review->isObjectNew()) {
      $product = Mage::getModel('catalog/product')->load($review->getEntityPkValue());
      $productUrl = $product->getProductUrl();
      $productImg = Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(100);

      if (Mage::helper('reviewbooster')->enableAdminNotification()) {      
        $this->_sendAdminEmail($review, $product, $productUrl, $productImg);
      }
    }
    return $this;
  }

  protected function _sendAdminEmail($review, $product, $productUrl='', $productImg='') {   
    $emailTemplate= Mage::getModel('core/email_template');
    $emailTemplate->setTemplateSubject('Your Email Template is not loaded');

    $templateId = Mage::getStoreConfig('reviewbooster/admin_notifier/email_template');
    if (is_numeric($templateId)) {
      $emailTemplate->load($templateId);
    } else {
      $emailTemplate->loadDefault($templateId);
    }

		$storeId = Mage::app()->getStore()->getId();
    $emailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name', $storeId));
    $emailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email', $storeId));
    
    $vars['review'] = $review;
    $vars['product'] = $product;
    $vars['productUrl'] = $productUrl;
    $vars['productImg'] = $productImg;

    $admin_emails = preg_split("/[\s,\n]+/", Mage::getStoreConfig('reviewbooster/admin_notifier/admin_emails', $storeId));
    foreach ($admin_emails as $email) {
      if (Zend_Validate::is($email,'EmailAddress')) {
        $emailTemplate->send($email, 'Admin', $vars);
      }
    }
  }

  // create reminder when order status changes
  protected function _getOrderStatusNeedToRemind() {
		$storeId = Mage::app()->getStore()->getId();
    return Mage::getStoreConfig('reviewbooster/reminder/order_status', $storeId);
  }

	// check order status to create review reminder
  public function createReminder($observer) {
    $order = $observer->getOrder();
    if ($order->getStatus() == $this->_getOrderStatusNeedToRemind()) {
      $this->_createReminder($order);
    }
  }

  protected function _createReminder($order) {
    // return if function is not enabled
    if (!Mage::helper('reviewbooster')->isAutoReminderEnabled()) return false;

    foreach ($order->getItemsCollection(null, true) as $item) {
      try {
        $model = Mage::getModel('reviewbooster/reminder');
        $model->setStoreId($order->getStoreId());
        $model->setOrderId($item->getOrderId());
        $model->setProductId($item->getProductId());
        $model->setProductName($item->getName());
        $model->setCustomerEmail($order->getCustomerEmail());
        $model->save();
      } catch (Exception $e) {
				// do nothing here
      }
    }
  }

  //sending reminder to customers
  public function sendReminder_old() {
	  $reminder = Mage::getModel('reviewbooster/reminder')->getCollection()
		  ->addFieldToFilter('reminder_status', 'pending');
	  $reminderIds = $reminder->getData('reminder_id');
		$collection = Mage::getModel('reviewbooster/reminder')->getCollection();
	  $reminder->sendRemindersToCustomers(false, $reminderIds);
  }
	
	//send reminder
	public function sendReminder() {
		$collection = Mage::getModel('reviewbooster/reminder')->getCollection()
			->addFieldToFilter('reminder_status', 'pending')
			->addFieldToSelect('order_id')
			->addFieldToSelect('customer_email')
			->addFieldToSelect('reminder_code')
			->addFieldToSelect('store_id');
		
		$helper = Mage::helper('reviewbooster');
		if ($helper->isOnlySendToNewsletterSubscriber()) {
			/* not remind customer who does not subscribe email letter */
			$collection->getSelect()->joinLeft(
				array('newsletter_sub_table' => Mage::getSingleton('core/resource')->getTableName('newsletter_subscriber')),
				'main_table.customer_email = newsletter_sub_table.subscriber_email',
				array('subscriber_status')
			);
			$collection->addFieldToFilter('subscriber_status','1');
		}
		
		/* not remind customer who is in review booster unsubscribed list */
		$collection->getSelect()->joinLeft(
			array('reviewbooster_unsub_table' => Mage::getSingleton('core/resource')->getTableName('magebuzz_reviewbooster_unsubscribed_customer')),
			'main_table.customer_email = reviewbooster_unsub_table.unsub_email',
			array('unsub_id')
		);
		$collection->addFieldToFilter('unsub_id', array('null' => true));
		
		$collection->getSelect()->group('main_table.order_id');
		
		foreach ($collection->getItems() as $item) {
			$order = Mage::getModel('sales/order')->load($item->getOrderId());
			$reminders = Mage::getModel('reviewbooster/reminder')->getCollection()
				->addFieldToSelect('*')
				->addFieldToFilter('order_id', $item->getOrderId())
				->addFieldToFilter('reminder_status', 'pending')
				->addFieldToFilter('sending_at', array(
					'lteq' => date('Y-m-d h:i:s', Mage::getSingleton('core/date')->timestamp(time())),
					'datetime' => true
				));
			
			$unsubscribeUrl = Mage::getUrl('reviewbooster/index/unsubscribe', array('code' => $item->getReminderCode()));
			
			try {
				$mailTemplate = Mage::getModel('core/email_template');
				$mailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $item->getStoreId()))
					->sendTransactional(
						$templateId = Mage::getStoreConfig('reviewbooster/reminder/email_template', $item->getStoreId()),
						$sender = Mage::getStoreConfig('reviewbooster/reminder/email_sender', $item->getStoreId()),
						$item->getCustomerEmail(),
						null,
						array('reminder_collection' => $reminders, 'order' => $order, 'unsubscribeUrl' => $unsubscribeUrl),
						$order->getStoreId()
					);
				/* update reminders' statuses */
				if ($mailTemplate->getSentSuccess()) {
					foreach ($reminders->getItems() as $reminder) {
						$reminder->setReminderStatus('sent');
						$reminder->save();
					}
				} else {
					foreach ($reminders->getItems() as $reminder) {
						$reminder->setReminderStatus('not_sent');
						$reminder->save();
					}
				}
			} catch (Exception $e) {
				//nothing to declare
			}
		}
	}

  /* add button sending reminder email in adminhtml order view */
  public function addButtonsSendingReviewReminder($event) {
    $block = $event->getBlock();
    if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View) {
      $message = Mage::helper('reviewbooster')->__('Are you sure you want to send review reminder email?');
      $block->addButton('send_reminder', array(
        'label'     => Mage::helper('reviewbooster')->__('Send Review Reminder Email'),
        'onclick'   => "confirmSetLocation('{$message}', '{$block->getUrl('reviewbooster/reviewboosteradmin/send_reminder_by_order', array('order_id' => $block->getOrder()->getId()))}')",
        'class'     => 'go'
      ));

      $block->addButton('send_test_reminder', array(
        'label'     => Mage::helper('reviewbooster')->__('Send Testing Reminder Email'),
        'onclick'   => "reviewbooster_open_popup()",
        'class'     => 'go'
      ));
    }
  }

}