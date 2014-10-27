<?php
class Wee_DeveloperToolbar_Block_Tab_Logs extends Wee_DeveloperToolbar_Block_Tab
{
    public function __construct($name, $label)
    {
       parent::__construct($name, $label);
       $this->setTemplate('wee_developertoolbar/tab/logs.phtml');
       $this->setIsActive(true);
    }

    public function getTailLines()
    {
        return 30;
    }

    public function getLogFiles()
    {
        return array('system.log', 'exception.log');
    }

    public function getJsonLogFiles()
    {
        return Mage::helper('core')->jsonEncode($this->getLogFiles());
    }

    public function getLogContent($file)
    {
        return Mage::helper('wee_developertoolbar/log')->tailFile(Mage::getBaseDir('log') . DS .$file, $this->getTailLines());
    }

    public function getUrlLog($action=false)
    {
        return $this->getUrl('wee_developertoolbar/log' . (($action) ? '/' . $action : '') );
    }


    public function getIdContainer()
    {
        return 'tab_' . $this->getName();
    }
}