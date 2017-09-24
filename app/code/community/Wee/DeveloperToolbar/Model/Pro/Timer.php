<?php
class Wee_DeveloperToolbar_Model_Pro_Timer
{
    const BLOCK_CLASS = 1;

    const BLOCK_TEMPLATE = 2;

    const BLOCK_CREATE = 3;

    const BLOCK_RENDER = 4;

    const PARENT_BLOCK = 5;

    const TIMER_START = 6;

    const BLOCK_CHILDREN = 7;

    protected $inactive = false;

    protected $block_timers = array();

    protected function _initTimerProfiling()
    {
        if ($this->inactive)
            return;

        $this->block_timers = array();
    }

    public function timer_predispatch()
    {
        $this->_initTimerProfiling();
    }

    public function getTimerData()
    {
        //$this->timers[$block_name][$n] = $this->block_timers[$block_name][$n];
        uasort($this->block_timers, array($this, '_sort'));

        return $this->block_timers;
    }

    protected function _sort($a, $b)
    {
        if(empty($a[self::BLOCK_CREATE])) {
            $a[self::BLOCK_CREATE] = 0;
        }
        if(empty($b[self::BLOCK_CREATE])) {
            $b[self::BLOCK_CREATE] = 0;
        }
        if(empty($a[self::BLOCK_RENDER])) {
            $a[self::BLOCK_RENDER] = 0;
        }
        if(empty($b[self::BLOCK_RENDER])) {
            $b[self::BLOCK_RENDER] = 0;
        }

        $totalTimeA = $a[self::BLOCK_CREATE] + $a[self::BLOCK_RENDER];
        $totalTimeB = $b[self::BLOCK_CREATE] + $b[self::BLOCK_RENDER];
        if( $totalTimeA > $totalTimeB) {
            return -1;
        } elseif( $totalTimeA < $totalTimeB) {
            return 1;
        } else {
            return 0;
        }
    }

    public function timer_block_create_before($observer)
    {
        if ($this->inactive)
            return;

        $this->_updateTimerBlock($observer, self::BLOCK_CREATE, 'start');
    }

    public function timer_block_create_after($observer)
    {
        if ($this->inactive)
            return;

        $this->_updateTimerBlock($observer, self::BLOCK_CREATE, 'stop');
    }

    public function timer_block_render_before($observer)
    {
        if ($this->inactive)
            return;

        $this->_updateTimerBlock($observer, self::BLOCK_RENDER, 'start');
    }

    public function timer_block_render_after($observer)
    {
        if ($this->inactive)
            return;

        $block = $observer->getEvent()->getBlock();
        $block_values = &$this->block_timers[$block->getNameInLayout()];

        $this->_updateTimerBlock($observer, self::BLOCK_RENDER, 'stop');

        if (($parent = $block->getParentBlock()))
            $block_values[self::PARENT_BLOCK] = $parent->getNameInLayout();
    }

    protected function _updateTimerBlock($observer, $n, $type)
    {
        $block = $observer->getEvent()->getBlock();
        if($block) {
            $block_name = $block->getNameInLayout();
        } else {
            $block_name = $observer->getEvent()->getBlockName();
        }

        if(empty($block_name)) {
            return;
        }

        if (!isset($this->block_timers[$block_name])) {
            $this->block_timers[$block_name] = array(0, '', '', 0, 0, '', 0);
        } elseif ($n == self::BLOCK_CREATE) {
            $this->block_timers[$block_name][0] += 1;
        }

        if (!isset($this->block_timers[$block_name][$n])) {
            $this->block_timers[$block_name][$n] = 0;
        }



        if($type=='start') {

//             if(get_class($block) == 'Mage_Catalog_Block_Category_View') {
//                 var_dump($block_name);
//                 exit;
//             }


//             if($block_name=='category.products') {
//                 Mage::log('Start category.products', null, 'adm.log');
//                 xdebug_start_trace();
//             }


            $this->block_timers[$block_name][self::TIMER_START] = microtime(true);
        } else {
//             if($block_name=='category.products') {
//                 Mage::log('End category.products', null, 'adm.log');
//                 xdebug_stop_trace();
//             }


            if(!empty($this->block_timers[$block_name][self::TIMER_START])) {
                $this->block_timers[$block_name][$n] += round((microtime(true)-$this->block_timers[$block_name][self::TIMER_START]), 5);
            }
            $this->block_timers[$block_name][self::BLOCK_CLASS] = get_class($block);
            $this->block_timers[$block_name][self::BLOCK_CHILDREN] = $block->countChildren();

            // Need a reflexion class, getTemplate can generate an error
            // ie: Mage_Wishlist_Block_Customer_Wishlist_Item_Options::getTemplate() : $this->getItem() is empty and genarate a fatal error
            // $this->block_timers[$block_name][self::BLOCK_TEMPLATE] = $block->getTemplate();

            try {
                $reflection = new ReflectionClass(get_class($block));
                $templateProperty = $reflection->getProperty('_template');
                if($templateProperty) {
                    $templateProperty->setAccessible(true);
                    $reflectionTemplate= trim($templateProperty->getValue($block));
                    if(!empty($reflectionTemplate)) {
                        $this->block_timers[$block_name][self::BLOCK_TEMPLATE] = $reflectionTemplate;
                    }

                }
            } catch (Exception $e) {
                $this->block_timers[$block_name][self::BLOCK_TEMPLATE] = $block->getData('template');
            }

            if(empty($this->block_timers[$block_name][self::BLOCK_TEMPLATE]) and strpos($block_name,'ANONYMOUS_')!==false) {
                $this->block_timers[$block_name][self::BLOCK_TEMPLATE] = $block->getBlockId();
            }
        }
    }
}
