<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/
require_once(Mage::getModuleDir('controllers','Mage_Review').DS.'ProductController.php');
class Magebuzz_Reviewbooster_IndexController extends Mage_Core_Controller_Front_Action {

  public function indexAction() {

  }

  public function replyByCustomerAction() {
    $code = $this->getRequest()->getParam('code');
    $invalidCodeUrl = Mage::getUrl('/');
    if ($code) {
      $reminder = Mage::getModel('reviewbooster/reminder')->load($code,'reminder_code');
      if ($reminder->getId()) {
	      Mage::getModel('core/cookie')->set('reminder_id', $reminder->getId());
	      Mage::getModel('core/cookie')->set('customer_email', $reminder->getData('customer_email'));
	      Mage::getModel('core/cookie')->set('order_id', $reminder->getData('order_id'));
	      Mage::getModel('core/cookie')->set('product_id', $reminder->getData('product_id'));

//        $reminder->setReminderStatus('replied');
//        $reminder->save();
//	      Zend_Debug::dump(Mage::getModel('core/cookie')->get('customer_email'));die();
        $this->_redirect('review/product/list',array('id' => $reminder->getProductId()));
      } else {
        $this->_redirect($invalidCodeUrl);
      }
    } else {
      $this->_redirect($invalidCodeUrl);
    }
  }

  public function unsubscribeAction() {
    $code = $this->getRequest()->getParam('code');
    $invalidCodeUrl = '/';
    if ($code) {
      $reminder = Mage::getModel('reviewbooster/reminder')->load($code,'reminder_code');
      if ($reminder->getId()) {
        $model = Mage::getModel('reviewbooster/unsubscriber');
        $model->setUnsubEmail($reminder->getCustomerEmail());
        $model->save();
        $this->_redirect('reviewbooster/index/sorry');
      } else {
        $this->_redirect($invalidCodeUrl);
      }
    } else {
      $this->_redirect($invalidCodeUrl);
    }
  }

  public function sorryAction() {
    $this->loadLayout();
    $this->renderLayout();
  }

  public function testAction() {
    $collection = Mage::getModel('reviewbooster/reminder')->getCollection()
			->addFieldToFilter('reminder_status', 'pending')
			->addFieldToSelect('order_id')
			->addFieldToSelect('customer_email')
			->addFieldToSelect('reminder_code')
			->addFieldToSelect('store_id');
		
		// sort by order to send one email for each order
		//$collection->getSelect()->distinct('order_id');
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
				->addFieldToFilter('reminder_status', 'pending');
				// ->addFieldToFilter('sending_at', array(
					// 'lteq' => date('Y-m-d h:i:s', Mage::getSingleton('core/date')->timestamp(time())),
					// 'datetime' => true
				// ));
			
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
				// if ($mailTemplate->getSentSuccess()) {
					// foreach ($reminders->getItems() as $reminder) {
						// $reminder->setReminderStatus('sent');
						// $reminder->save();
					// }
				// } else {
					// foreach ($reminders->getItems() as $reminder) {
						// $reminder->setReminderStatus('not_sent');
						// $reminder->save();
					// }
				// }
			} catch (Exception $e) {
				//nothing to declare
			}
		}
		
  }

  public function save_reviewAction() {
    $data = $this->getRequest()->getPost();

    /* check the valid of data by checking the hash code: reminder_code */
    if (isset($data['reminder_code'])&&($data['reminder_code'] != '')) {
      $reminder = Mage::getModel('reviewbooster/reminder')->load($data['reminder_code'],'reminder_code');
    } else if ($this->getRequest()->getParam('reminder_code')) {
      $reminder = Mage::getModel('reviewbooster/reminder')->load($this->getRequest()->getParam('reminder_code'),'reminder_code');
    } else {
      $this->_redirect('/');
      return;
    }
    if (($reminder)&&($reminder->getId())) {

      $session    = Mage::getSingleton('core/session');
      $review     = Mage::getModel('review/review')->setData($data);
      $validate = $review->validate();
      if (($validate === true) && isset($data['ratings'])) {
        try {
          $review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
            ->setEntityPkValue($reminder->getProductId())
            ->setStatusId(Mage_Review_Model_Review::STATUS_PENDING)
            ->setCustomerId($reminder->getCustomerId())
            ->setStoreId(Mage::app()->getStore()->getId())
            ->setStores(array(Mage::app()->getStore()->getId()))
            ->save();

          foreach ($data['ratings'] as $ratingId => $optionId) {
            Mage::getModel('rating/rating')
              ->setRatingId($ratingId)
              ->setReviewId($review->getId())
              ->setCustomerId($reminder->getCustomerId())
              ->addOptionVote($optionId, $reminder->getProductId());
          }
	        $reminder->setReminderStatus('replied');
	        $reminder->save();
          $review->aggregate();
          $session->addSuccess($this->__('Your review has been accepted for moderation.'));
          $this->_redirect('review/product/list',array('id' => $reminder->getProductId()));
          return;
        }
        catch (Exception $e) {
          $session->addError($this->__('Unable to post the review. Please fill all required fields.'));
          $this->_redirect('review/product/list',array('id' => $reminder->getProductId()));
          return;
        }
      }
      else {
        $session->addError($this->__('Unable to post the review. Please fill all required fields.'));
        $this->_redirect('review/product/list',array('id' => $reminder->getProductId()));
        return;
      }
    } else {
      $this->_redirect('/');
      return;
    }
  }
}
