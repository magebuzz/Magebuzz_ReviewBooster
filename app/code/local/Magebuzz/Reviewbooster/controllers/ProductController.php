<?php
/*
* @copyright (c) 2015 www.magebuzz.com
*/

require_once(Mage::getModuleDir('controllers','Mage_Review').DS.'ProductController.php');

class Magebuzz_Reviewbooster_ProductController extends Mage_Review_ProductController {
  public function postAction() {
	  $product_id = Mage::getModel('core/cookie')->get('product_id');
		
		//validate captcha if enabled
		if (Mage::helper('reviewbooster')->canShowCaptcha()) {
			$captchaResponse = $_POST['g-recaptcha-response'];
			$captchaResult = Mage::helper('reviewbooster')->postCaptchaData($captchaResponse);

			$result = array();
			if (!$captchaResult->success) {
				$result['error'] = true;
				$result['message'] = $this->__("The reCAPTCHA wasn't entered correctly. Go back and try it again.");
				$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
				return;
			}
		}
				
    if (!$this->_validateFormKey()) {
      // returns to the product item page			
      $result['error'] = true;
			$result['message'] = $this->__('Unable to post the review.');
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
			return;
    }

    if ($data = Mage::getSingleton('review/session')->getFormData(true)) {
      $rating = array();
      if (isset($data['ratings']) && is_array($data['ratings'])) {
        $rating = $data['ratings'];
      }
    } else {
      $data   = $this->getRequest()->getPost();
      $rating = $this->getRequest()->getParam('ratings', array());
    }
		
    if (($product = $this->_initProduct()) && !empty($data)) {
      $session    = Mage::getSingleton('core/session');
      /* @var $session Mage_Core_Model_Session */
      $review     = Mage::getModel('review/review')->setData($data);
      /* @var $review Mage_Review_Model_Review */

      $validate = $review->validate();
      if ($validate === true) {
        try {
          $review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
            ->setEntityPkValue($product->getId())
            ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
            ->setStoreId(Mage::app()->getStore()->getId())
            ->setStores(array(Mage::app()->getStore()->getId()));

          if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            if (Mage::helper('reviewbooster')->isAutoApprovedConfig()) {
              $review->setStatusId(Mage_Review_Model_Review::STATUS_APPROVED);
              $result['message'] = $this->__('Your review has been approved.');
            } else {
              $review->setStatusId(Mage_Review_Model_Review::STATUS_PENDING);
						  $result['message'] = $this->__('Your review has been accepted for moderation.');
            }
          } else {
            $review->setStatusId(Mage_Review_Model_Review::STATUS_PENDING);
						$result['message'] = $this->__('Your review has been accepted for moderation.');
          }
          $review->save();

          foreach ($rating as $ratingId => $optionId) {
            Mage::getModel('rating/rating')
              ->setRatingId($ratingId)
              ->setReviewId($review->getId())
              ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
              ->addOptionVote($optionId, $product->getId());
          }
          $review->aggregate();

          if (Mage::getSingleton('customer/session')->isLoggedIn() && Mage::helper('reviewbooster')->isAutoApprovedConfig()) {
            $layout = $this->getLayout();
            $layout->getUpdate()->load('reviewbooster_product_post');
            $layout->generateXml();
            $layout->generateBlocks();
            $toolbar = $layout->getBlock('product_review_list.toolbar');

            $toolbar->setCollection($this->getReviewsCollection());
            $result['review_list_html'] = $layout->getOutput();
          }

	        //update status reminder
	        $product_id = Mage::getModel('core/cookie')->get('product_id');
	        if($product_id == $this->getRequest()->getParam('id')) {
		        $reminde_id = Mage::getModel('core/cookie')->get('reminder_id');
		        $reminder = Mage::getModel('reviewbooster/reminder')->load($reminde_id,'reminder_id');
		        $reminder->setReminderStatus('replied');
		        $reminder->save();
	        }

					$result['success'] = true;
					$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
					return;

        }
        catch (Exception $e) {
					echo $e->getMessage();
					die('eee');
          $session->setFormData($data);
					$result['error'] = true;
					$result['message'] = $this->__('Unable to post the review.1');
					$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
					return;
        }
      }
      else {
				$result['error'] = true;
				$result['message'] = $this->__('Unable to post the review.2');
				$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
				return;
      }
    }
  }

  protected  function getReviewsCollection() {
    $reviewsCollection = Mage::getModel('review/review')->getCollection()
      ->addStoreFilter(Mage::app()->getStore()->getId())
      ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
      ->addEntityFilter('product', Mage::registry('product')->getId())
      ->setDateOrder();
    return $reviewsCollection;
  }
}