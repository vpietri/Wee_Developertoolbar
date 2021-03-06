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

class Wee_DeveloperToolbar_Block_Tab_Blocks extends Wee_DeveloperToolbar_Block_Tab
{
    public function __construct($name, $label)
    {
        parent::__construct($name, $label);
        $this->setTemplate('wee_developertoolbar/tab/blocks.phtml');
    }

    public function getRootBlock()
    {
        return Mage::app()->getLayout()->getBlock('root');
    }

    public function printBlocks($block)
    {
        $out = '';
        if($block) {
            if ($block->getChild()) {
                $sortedChildren = $block->getSortedChildren();
                $out .= '<ul>';
                foreach ($sortedChildren as $childname) {
                    $child = $block->getChild($childname);
                    if (!$child){
                      continue;
                    }
                    $hasChildren =  $child->getChild() ? true : false;
                    $out .= '<li '.($hasChildren ? 'class="rootElement"' : '').'>';
                    $out .= '<a href="javascript:void(0);" class="toggleBlogProperties">'.$child->getNameInLayout().'</a>';
                    $out .= $this->printBlockProperties($child);
                    if ($hasChildren) {
                        $out .= $this->printBlocks($child);
                    }
                    $out .= '</li>';
                }
                $out .= '</ul>';
            }
        } else {
            $out = 'No block information available. FPC?';
        }
        return $out;
    }

    protected function printBlockProperties(Mage_Core_Block_Abstract $block)
    {
        $properties = '<ul class="blockProperties" style="display:none;">';
        $properties .= '<li><strong>Class:</strong> '.get_class($block).'</li>';
        if ($block->getTemplate()) {
            $properties .= '<li><strong>Template:</strong> '.$block->getTemplate().'</li>';
        }
        $properties .= '</ul>';
        return $properties;
    }
}