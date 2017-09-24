<?php

class Wee_DeveloperToolbar_Model_Pro_Session extends Mage_Core_Model_Session_Abstract
{
    public function __construct()
    {
        $namespace = 'wee_developertoolbar_pro';
        $this->init($namespace);
    }

}
