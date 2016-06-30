<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Reviewbooster_Block_Review_Product_Form extends Mage_Review_Block_Form {

  public function getAction() {
    $productId = Mage::app()->getRequest()->getParam('id', false);
    return Mage::getUrl('reviewbooster/product/post', array('id' => $productId));
  }
	
	public function canShowCaptcha() {
		return Mage::helper('reviewbooster')->canShowCaptcha();
	}
}