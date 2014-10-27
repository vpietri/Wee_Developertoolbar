<?php
class Wee_DeveloperToolbar_LogController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        if (Mage::helper('wee_developertoolbar')->isToolbarAccessAllowed()) {
                $file = $this->getRequest()->getParam('file');
                $lines = $this->getRequest()->getParam('lines', 20);
                if($file) {
                     $html = Mage::helper('wee_developertoolbar')->tailFile(Mage::getBaseDir('log') . DS .$file, $lines);
                } else {
                    $html = 'empty file';
                }
                $this->getResponse()->setHeader('Content-type', 'text/html');
                $this->getResponse()->setBody($html);

        }
    }

    public function resetAction()
    {
        if (Mage::helper('wee_developertoolbar')->isToolbarAccessAllowed()) {
            $file = $this->getRequest()->getParam('file');

            if(!empty($file)) {
                $filePath = Mage::getBaseDir('log') . DS .$file;
                if(file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }
    }
}




