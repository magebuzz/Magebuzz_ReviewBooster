<?php
/*
* @copyright (c) 2015 www.magebuzz.com
*/
class Magebuzz_Reviewbooster_Block_Captcha extends Mage_Core_Block_Template {
	public function __construct() {
		parent::__construct();
	}
	
	public function getRecaptchaPublicKey() {
		return Mage::helper('reviewbooster')->getRecaptchaPublicKey();		
	}
}