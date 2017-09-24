<?php

class Wee_DeveloperToolbar_Model_Pro_Core_Layout extends Mage_Core_Model_Layout {

	public function createBlock($type, $name='', array $attributes = array())
	{
	  $blocAttributes= new Varien_Object($attributes);
	  if (empty($name)) {
    	  $name = 'ANONYMOUS_'.sizeof($this->_blocks);
	  }
		Mage::dispatchEvent('adm_core_layout_block_create_before', array('block_type' => $type, 'block_name' => $name, 'block_attributes' => $blocAttributes));

		$block = parent::createBlock($type, $name, $blocAttributes->__toArray());

		Mage::dispatchEvent('adm_core_layout_block_create_after', array('block' => $block));


// 		if($block->getTemplate() == 'catalog/product/price.phtml') {
// 		    var_dump('coucou');
// 		}
// 		var_dump($block->getTemplate());

		return $block;

	}

}
