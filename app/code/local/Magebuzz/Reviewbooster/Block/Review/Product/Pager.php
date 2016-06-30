<?php

class Magebuzz_Reviewbooster_Block_Review_Product_Pager extends Mage_Page_Block_Html_Pager {
  public function getPagerUrl($params=array())
  {
    $urlParams = array();
    $urlParams['_query']    = $params;
    $product = Mage::registry('product');
    return $this->getUrl($product->getUrlPath(), $urlParams);
  }
}