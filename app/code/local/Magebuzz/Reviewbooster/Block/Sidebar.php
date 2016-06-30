<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/
class Magebuzz_Reviewbooster_Block_Sidebar extends Mage_Core_Block_Template {

  public function getBlockTitle() {
    return Mage::getStoreConfig('reviewbooster/sidebar/block_title');
  }

  protected function _getNumberItem() {
    return Mage::getStoreConfig('reviewbooster/sidebar/number_item');
  }

  protected function _enabledCategoryFilter() {
    return Mage::getStoreConfig('reviewbooster/sidebar/category_filter_enabled');
  }

  protected function _enabledDisplayProductImage() {
    return Mage::getStoreConfig('reviewbooster/sidebar/product_image_enabled');
  }

  protected function _getMinPercentRating() {
    return Mage::getStoreConfig('reviewbooster/sidebar/min_percent_rating');
  }  

  protected function _getReviewCollection() {
    $collection = Mage::getSingleton('rating/rating_option_vote')->getCollection();

    $collection->getSelect()
      ->columns('SUM(percent)/COUNT(*) AS average_percent')
      ->group('review_id');

    $collection->getSelect()->joinLeft(
      array('rd'=>'review_detail'),
      'rd.review_id = main_table.review_id',
      array('rd.title','rd.detail','rd.nickname')
    );

    $collection->getSelect()->joinLeft(
      array('ccp' => 'catalog_category_product'),
      'main_table.entity_pk_value = ccp.product_id',
      array('category_id')
    );

    $collection->getSelect()->joinLeft(
      array('r' => 'review'),
      'main_table.review_id = r.review_id',
      array('status_id','created_at')
    );

    $collection->getSelect()->joinLeft(
      array('cpei' => 'catalog_product_enabled_index'),
      'main_table.entity_pk_value = cpei.product_id',
      array('store_id','visibility')
    );

    // filter by approved-status of review
    $collection->addFieldToFilter('status_id', Mage_Review_Model_Review::STATUS_APPROVED);

    // filter by visibility of product
    $collection->addFieldToFilter('visibility', Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds());

    // filter by minimum percentage
    $collection->getSelect()->having('average_percent >= ?', $this->_getMinPercentRating());

    // filter by category and its children categories
    $current_category = Mage::registry('current_category');
    if ((!empty($current_category))&&($this->_enabledCategoryFilter())) {
      $collection->addFieldToFilter('category_id', array('in' => explode(",",$current_category->getAllChildren())));
    }

    // random order and limit by number_review set by user
    $collection->setOrder('rand()');
    $collection->getSelect()->limit($this->_getNumberItem());

    return $collection;
  }
}