<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Reviewbooster_Block_Adminhtml_Reminder_Render_Vieworder extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
  public function render(Varien_Object $row) {
		$url_view = Mage::helper('adminhtml')->getUrl('/sales_order/view', array('order_id' => $row->getOrderId()));
		return '<a href="'.$url_view.'">'.$row->getIncrementId().'</a>';
	}
}
