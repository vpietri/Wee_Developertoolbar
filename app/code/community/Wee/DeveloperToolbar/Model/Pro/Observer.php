<?php

class Wee_DeveloperToolbar_Model_Pro_Observer {
    /**
     *
     * @param  Varien_Event_Observer $observer
     */
    public function saveTrace($observer)
    {
        $isProfiling = false;
        if ($isProfiling) {
//         $block = $observer->getEvent()->getBlock();
//         if ($block->getNameInLayout()=='core_profiler') {
            Mage::helper('wee_developertoolbar/pro_data')->saveSqlTrace();

//         }
        }
    }
}