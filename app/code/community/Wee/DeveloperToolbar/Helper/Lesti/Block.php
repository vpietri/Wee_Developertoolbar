<?php

class Wee_DeveloperToolbar_Helper_Lesti_Block extends Lesti_Fpc_Helper_Block
{
    /**
     * @return array
     */
    public function getDynamicBlocks()
    {
        $blocks = $this->getCSStoreConfigs(self::DYNAMIC_BLOCKS_XML_PATH);
        $blocks[] = 'wee_developertoolbar';

        return $blocks;
    }
}