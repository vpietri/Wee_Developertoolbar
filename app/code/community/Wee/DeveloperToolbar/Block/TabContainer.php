<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Wee
 * @package     Wee_DeveloperToolbar
 * @author      Stefan Wieczorek <stefan.wieczorek@mgt-commerce.com>
 * @copyright   Copyright (c) 2011 (http://www.mgt-commerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Wee_DeveloperToolbar_Block_TabContainer extends Wee_DeveloperToolbar_Block_Template
{
    protected $_tabs = array();
    protected $name;

    public function __construct($name)
    {
        parent::__construct();
        $this->name = $name;
        if (!$this->hasData('template')) {
            $this->setTemplate('wee_developertoolbar/tabcontainer.phtml');
        }


        $toolbarConfig = Mage::helper('wee_developertoolbar')->getToolbarConfig($name);
        foreach($toolbarConfig->getTabContainer() as $tabKey => $tabConfig) {

            try {
                if(empty($tabConfig['class_tab'])) {
                    $blockClassName = 'Wee_DeveloperToolbar_Block_Tab_' . uc_words($tabKey,'');
                } else {
                    $blockClassName = $tabConfig['class_tab'];
                }

                if(class_exists($blockClassName)) {
                    $blockItem = new $blockClassName(str_replace('_', '', $tabKey), $tabConfig['label']);
                } else {
                    throw new Exception($this->__('Specific tab block "%s" not handle by wee developer container.', $blockClassName));
                }
                $this->addTab($blockItem);

            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    public function addTab(Wee_DeveloperToolbar_Block_Tab $tab)
    {
       $this->_tabs[] = $tab;
    }

    public function getTabs()
    {
        return $this->_tabs;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}