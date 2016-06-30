<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/
class Magebuzz_Reviewbooster_ReviewboosteradminController extends Mage_Adminhtml_Controller_Action {
  
	public function manageReminderAction() {
    $this->loadLayout()
      ->_setActiveMenu('reviewbooster/reminder_management');
    $this->_title($this->__('Catalog'))
      ->_title($this->__('Review Booster'))
      ->_title($this->__('Manage Reminder'));
    $this->renderLayout();
  }

  public function manageUnsubscriberAction() {
    $this->loadLayout()
      ->_setActiveMenu('reviewbooster/unsubscriber_management');
    $this->_title($this->__('Catalog'))
      ->_title($this->__('Review Booster'))
      ->_title($this->__('Manage Unsubscribed Customers'));
    $this->renderLayout();
  }

  public function socialAction() {
    $this->loadLayout()
      ->_setActiveMenu('reviewbooster/social');
    $this->_title($this->__('Catalog'))
      ->_title($this->__('Review Booster'))
      ->_title($this->__('Social Sharing'));
    $this->renderLayout();
  }

  public function massDeleteUnsubscriberAction() {
    $unsubscriberIds = $this->getRequest()->getParam('unsubscriber');
    if (!is_array($unsubscriberIds)) {
      $this->_getSession()->addError($this->__('Please select subscriber(s)'));
    } else {
      if (!empty($unsubscriberIds)) {
        try {
          foreach ($unsubscriberIds as $id ) {
            $model = Mage::getModel('reviewbooster/unsubscriber')->load($id);
            $model->delete();
          }
          $this->_getSession()->addSuccess(
          $this->__('Total of %d unsubscriber(s) have been deleted.', count($unsubscriberIds)));
        } catch (Exception $e) {
          $this->_getSession()->addError($e->getMessage());
        }
      }
    }
    $this->_redirect('*/*/manageUnsubscriber');
  }

  public function massDeleteAction() {
    $reminderIds = $this->getRequest()->getParam('reminder');
    if (!is_array($reminderIds)) {
      $this->_getSession()->addError($this->__('Please select reminder(s)'));
    } else {
      if (!empty($reminderIds)) {
        try {
          foreach ($reminderIds as $id ) {
            $model = Mage::getModel('reviewbooster/reminder')->load($id);
            $model->delete();
          }
          $this->_getSession()->addSuccess(
          $this->__('Total of %d reminder(s) have been deleted.', count($reminderIds)));
        } catch (Exception $e) {
          $this->_getSession()->addError($e->getMessage());
        }
      }
    }
    $this->_redirect('*/*/manageReminder');
  }

  public function massSendAction() {
    $reminderIds = $this->getRequest()->getParam('reminder');
    if (!is_array($reminderIds)) {
      $this->_getSession()->addError($this->__('Please select reminder(s)'));
    } else {
      if (!empty($reminderIds)) {
        try {
          $collection = Mage::getModel('reviewbooster/reminder')->getCollection();
          $collection->addFieldToSelect('*')
            ->addFieldToFilter('reminder_id', array('in', $reminderIds));
						
          $collection->sendRemindersToCustomers(true, $reminderIds);
					
					$this->_getSession()->addSuccess(
						$this->__('Total of %d reminder(s) have been sent.', count($reminderIds))
					);
        } catch (Exception $e) {
          $this->_getSession()->addError($e->getMessage());
        }
      }
    }
    $this->_redirect('*/*/manageReminder');
  }

  public function send_reminder_by_orderAction() {
    $order_id = $this->getRequest()->getParam('order_id');
    if ($order_id) {
      try {
        Mage::helper('reviewbooster')->sendReminderByOrder($order_id,true);
        $this->_getSession()->addSuccess(Mage::helper('reviewbooster')->__('Send review reminder(s) successfully'));
      } catch (Exception $e) {
        $this->_getSession()->addError($e->getMessage());
      }
    } else {
      $this->_getSession()->addError($this->__("There is an error when sending reminder(s)"));
    }
    $this->_redirect('adminhtml/sales_order/view', array('order_id' => $order_id));
  }

	public function sendReminderAction(){
		$remider = Mage::getModel('reviewbooster/reminder')->getCollection()
			->addFieldToFilter('reminder_status', 'pending');
		$reminderIds = $remider->getData('reminder_id');
		$collection = Mage::getModel('reviewbooster/reminder')->getCollection();
		$remider->sendRemindersToCustomers(false, $reminderIds);
		$this->_redirect('*/*/manageReminder');
	}

  public function send_test_reminderAction() {
    $order_id = $this->getRequest()->getParam('order_id');
    $_email_address = $this->getRequest()->getParam('email_address');
    if ($order_id !='' && $_email_address != '') {
      try {
        Mage::helper('reviewbooster')->sendReminderByOrder($order_id,true, array(), $_email_address);
        $this->_getSession()->addSuccess(Mage::helper('reviewbooster')->__('Send review reminder(s) successfully'));
      } catch (Exception $e) {
        $this->_getSession()->addError($e->getMessage());
      }
    } else {
      $this->_getSession()->addError($this->__("There is an error when sending reminder(s)"));
    }
    $this->_redirect('adminhtml/sales_order/view', array('order_id' => $order_id));
  }
	
	protected function _isAllowed() {			
		return Mage::getSingleton('admin/session')->isAllowed('catalog/reviewbooster');	
	}
}