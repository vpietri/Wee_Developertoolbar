<?php

class Wee_DeveloperToolbar_Pro_IndexController extends Mage_Core_Controller_Front_Action
{

    public function tracePartAction()
    {

        echo Mage::getSingleton('wee_developertoolbar/pro_session')->getTraceSqlPart();

        $tracePart = $this->getRequest()->getParam('part');
        if ($tracePart) {
            Mage::getSingleton('wee_developertoolbar/pro_session')->setTraceSqlPart($tracePart);
            echo 'Set: '.$tracePart;
        } else {
            Mage::getSingleton('wee_developertoolbar/pro_session')->unsTraceSqlPart();
            echo 'Reset';
        }
    }
}