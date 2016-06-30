<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Reviewbooster_Block_Review_Product_List extends Mage_Review_Block_Product_View_List {
  public function getReviewsCollection() {
  	$collection = parent::getReviewsCollection();
  	return $collection;
  }
  public function getReviewUrl($id)
  {
    return Mage::getUrl('review/product/view', array('id' => $id));
  }
}