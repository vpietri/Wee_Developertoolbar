<?php
class Wee_DeveloperToolbar_Block_Pro_Tab_Timers extends Wee_DeveloperToolbar_Block_Tab
{
    public function __construct($name, $label)
    {
        parent::__construct($name, $label);
        $this->setTemplate('mrsgto/pro/tab/timers.phtml');
        $this->setIsActive(true);
    }

    public function getTimerData()
    {
        $observer = Mage::getSingleton('wee_developertoolbar/pro_timer');

        return $observer->getTimerData();
    }
}