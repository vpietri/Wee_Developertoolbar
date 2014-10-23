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

class Wee_DeveloperToolbar_Block_Toolbar_Item extends Wee_DeveloperToolbar_Block_Template
{
    protected $_name;
    protected $_content;
    protected $_label;
    protected $_icon;

    public function __construct($name, $label = '')
    {
        parent::__construct();
        $this->_name = $name;
        if ($label) {
            $this->_label = $label;
        }
        if (!$this->hasData('template')) {
            $this->setTemplate('wee_developertoolbar/item.phtml');
        }

        $toolbarConfig = Mage::helper('wee_developertoolbar')->getToolbarConfig($name);
        if($toolbarConfig->getIcon()) {
            $this->setIcon($this->getSkinUrl($toolbarConfig->getIcon()));
        }

        if($toolbarConfig->getTabContainer()) {
            $this->_content = new Wee_DeveloperToolbar_Block_TabContainer($name);
        }

        if($toolbarConfig->getLabel()) {
            $tabLabel = $toolbarConfig->getLabel();
            try {
                if ( preg_match('/(.*?)::(.*)/', $tabLabel, $matches)) {
                    $class = new $matches[1];
                    $label = $class->{$matches[2]}();
                    $this->setLabel($label);
                }
            } catch (Exception $e) {
                $this->setLabel(uc_words($label, ' '));
            }
        }

    }

    public function getName()
    {
        return $this->_name;
    }

    public function setName($name)
    {
        $this->_name = $name;
    }

    public function getLabel()
    {
        return $this->_label;
    }

    public function setLabel($label)
    {
        $this->_label = $label;
    }

    public function getIcon()
    {
        return $this->_icon;
    }

    public function setIcon($icon)
    {
        $this->_icon = $icon;
    }

    public function getContent()
    {
        return $this->_content;
    }

    public function setContent(Mage_Core_Block_Abstract $content)
    {
        $this->_content = $content;
    }

    public function renderContent()
    {
    	return $this->_content->toHtml();
    }

    public function render()
    {
    	return $this->toHtml();
    }

}