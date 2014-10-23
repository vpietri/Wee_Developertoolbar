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

class Wee_DeveloperToolbar_Block_Toolbar extends Wee_DeveloperToolbar_Block_Template
{
    protected $_items = array();

    public function __construct()
    {
        $this->_addDefaultItems();
    }

    protected function _addDefaultItems()
    {

        $toolbarConfig = Mage::helper('wee_developertoolbar')->getToolbarConfig();
        foreach($toolbarConfig as $itemKey => $itemConfig) {
            if(!$itemConfig->getClassItem()) {
                //$blockItem = $this->getLayout()->createBlock('wee_developertoolbar/toolbar_item_'.$itemKey, '', array($itemKey));
                //$blockClassName = 'Wee_DeveloperToolbar_Block_Toolbar_Item_' . uc_words($itemKey);
                $blockClassName = 'Wee_DeveloperToolbar_Block_Toolbar_Item';
                $blockItem = new $blockClassName($itemKey,$itemKey);
            } else {
                throw new Exception('Specific block not handle');
            }
            $this->_addItem($blockItem);
        }
    }

    protected function _addItem(Wee_DeveloperToolbar_Block_Toolbar_Item $item)
    {
        $this->_items[] = $item;
    }

    protected function getItems()
    {
        return $this->_items;
    }

    public function canViewToolbar()
    {
        return Mage::helper('wee_developertoolbar')->isRequestAllowed();
    }
}