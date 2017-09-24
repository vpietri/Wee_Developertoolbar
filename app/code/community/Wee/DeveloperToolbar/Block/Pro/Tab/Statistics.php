<?php
class Wee_DeveloperToolbar_Block_Pro_Tab_Statistics extends Wee_DeveloperToolbar_Block_Tab
{
    public function __construct($name, $label)
    {
        parent::__construct($name, $label);
        $this->setTemplate('wee_developertoolbar/pro/tab/statistics.phtml');
    }


    public function getStatisticsCalls()
    {
        $helper = Mage::helper('wee_developertoolbar/pro_analyzer');
        return $helper->getStatisticsCalls();
    }
}