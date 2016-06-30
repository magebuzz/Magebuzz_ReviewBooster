<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Reviewbooster_ReviewController extends Mage_Core_Controller_Front_Action {
  public function voteAction() {
    $this->getResponse()->setHeader('Content-type', 'application/json');
    $review_id = $this->getRequest()->getParam('review_id');
    $action_url = $this->getRequest()->getParam('action_url');
    $review = Mage::getModel('review/review')->load($review_id);
    if($action_url == 'like'){
      $review->setLikeNumber($review->getLikeNumber()+1);
      $review->save();
    }
    
    if($action_url == 'unlike'){
      $review->setLikeNumber($review->getLikeNumber()-1);
      $review->save();
    }
    
    if($action_url == 'dislike'){
      $review->setDislikeNumber($review->getDislikeNumber()+1);
      $review->save();
    }
    
    if($action_url == 'undislike'){
      $review->setDislikeNumber($review->getDislikeNumber()-1);
      $review->save();
    }
    
    $_response['success'] = 'true';
    $_response['like_number'] = $review->getLikeNumber();
    $_response['dislike_number'] = $review->getDislikeNumber();
    $this->getResponse()->setBody(json_encode($_response));
  }

}
