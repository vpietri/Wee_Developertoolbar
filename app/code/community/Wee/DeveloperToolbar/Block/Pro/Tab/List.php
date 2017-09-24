<?php
class Wee_DeveloperToolbar_Block_Pro_Tab_List extends Wee_DeveloperToolbar_Block_Tab
{
    public function __construct($name, $label)
    {
        parent::__construct($name, $label);
        $this->setTemplate('wee_developertoolbar/pro/tab/list.phtml');
        $this->setIsActive(true);
    }


    public function getSuspectedCalls()
    {
        $helper = Mage::helper('wee_developertoolbar/pro_analyzer');
        return $helper->getSuspectedCalls();
    }
}