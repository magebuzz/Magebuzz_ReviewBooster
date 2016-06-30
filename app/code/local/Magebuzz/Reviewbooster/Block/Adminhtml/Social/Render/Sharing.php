<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Reviewbooster_Block_Adminhtml_Social_Render_Sharing extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
  public function render(Varien_Object $row) {
    $url_share = Mage::getUrl('review/product/view', array('id' => $row->getReviewId()));
    $html =  '
    <div style="border: 0px;">
      <!-- facebook -->
      <div class="div-facebook">
        <div class="fb-share-button" data-href="'.$url_share.'" data-type="button_count"></div>
      </div>
      <!-- twitter -->
      <div>
        <a class="twitter-share-button" data-url="'.$url_share.'"></a>
      </div>
      <!-- Google+ -->
      <div>
        <div class="g-plusone" data-href="'.$url_share.'" data-size="medium"></div>
      </div>
    </div>
    ';
    return $html;
  }
}
