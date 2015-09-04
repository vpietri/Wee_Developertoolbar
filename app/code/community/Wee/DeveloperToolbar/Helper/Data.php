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
 * @author      Vincent Pietri (contributor)
 * @copyright   Copyright (c) 2011 (http://www.mgt-commerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Wee_DeveloperToolbar_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_toolbar_config;


    public function formatBytes($bytes)
    {
        $size = $bytes / 1024;
        if ($size < 1024) {
            $size = number_format($size, 2);
            $size .= ' KB';
        } else  {
            if ($size / 1024 < 1024)  {
                $size = number_format($size / 1024, 2);
                $size .= ' MB';
            }
            else if ($size / 1024 / 1024 < 1024) {
                $size = number_format($size / 1024 / 1024, 2);
                $size .= ' GB';
            }
        }
        return $size;
    }

    public function getMemoryUsage($realUsage = false)
    {
        return memory_get_usage($realUsage);
    }

    public function formatSql($sql)
    {
        return preg_replace('/\b(UPDATE|SET|SELECT|FROM|AS|LIMIT|ASC|COUNT|DESC|WHERE|LEFT JOIN|INNER JOIN|RIGHT JOIN|ORDER BY|GROUP BY|IN|LIKE|DISTINCT|DELETE|INSERT|INTO|VALUES)\b/', '<span class="weeDeveloperToolbarLogInfo">\\1</span>', $sql);
    }

    public function getMediaUrl()
    {
    	return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
    }


    function isToolbarAccessAllowed($beforeContext = false) {

        $allowedIps = '';
        $toolbarHeader = '';
        //When cache activated we do not have acces to current storeId and can throw an exception in Mage::app()->getStore()
        // on call to isToolbarAccessAllowed in Wee_DeveloperToolbar_Model_Resource
        try {
            $cacheKey = 'Wee_DeveloperToolbar_Config';
            if (Mage::app()->useCache('config')) {
                $toolbarConfig = Mage::app()->loadCache($cacheKey);
                if($toolbarConfig) {
                    $toolbarConfig = unserialize($toolbarConfig);
                    $allowedIps = $toolbarConfig['ips'];
                    $toolbarHeader = $toolbarConfig['header'];
                } else {
                    $allowedIps = Mage::getStoreConfig('dev/restrict/allow_ips');
                    $toolbarHeader = Mage::getStoreConfig('dev/restrict/toolbar_header');

                    Mage::app()->saveCache(serialize(array('ips'=>$allowedIps,'header'=>$toolbarHeader)), $cacheKey);
                }
            } else {

            }

        } catch (Exception $e) {
            //Mage::getIsDeveloperMode()
        }

        $clientIp = $this->_getRequest()->getClientIp();

        if ($clientIp=='127.0.0.1') {
            $allow = true;
        } elseif( !empty($allowedIps) and  !empty($clientIp)) {
            $allowedIps = preg_split('#\s*,\s*#', $allowedIps, null, PREG_SPLIT_NO_EMPTY);
            if (array_search($clientIp, $allowedIps) === false
                    && array_search(Mage::helper('core/http')->getHttpHost(), $allowedIps) === false) {
                $allow = false;
            } else {
                $allow = true;
            }
        } else {
            $allow = false;
        }

        if(!empty($toolbarHeader)) {
            $helper = Mage::helper('core/http');
        		if(!preg_match('/' . $toolbarHeader . '/', $helper->getHttpUserAgent(true))) {
        		    $allow = false;
        		}
        }
        return $allow;
    }

    function getToolbarConfig($name=false)
    {
        if (is_null($this->_toolbar_config)) {
            $path = 'default/developertoolbar/items';

            $this->_toolbar_config = array();
            if(Mage::getConfig()->getNode($path)) {
                $configNodes = Mage::getConfig()->getNode($path)->children();
                if ($configNodes) {
                    foreach($configNodes as $itemKey => $itemConfig){
                        $itemLine = $itemConfig->asArray();

                        $configKey = (string) $itemKey;

                        if(empty($itemLine['label'])) {
                            $itemLine['label'] = ucwords($configKey);
                        }

                        if(empty($itemLine['tab_container'])) {
                            $itemLine['tab_container'] = array();
                        }

                        $this->_toolbar_config[$configKey] = new Varien_Object($itemLine);
                    }
                }
            }
            uasort($this->_toolbar_config, array($this,'_sortToolbarConfig'));
        }

        return ($name && isset($this->_toolbar_config[$name])) ? $this->_toolbar_config[$name] : $this->_toolbar_config;
    }

    protected function _sortToolbarConfig($a, $b)
    {
        if($a->getSortOrder()<$b->getSortOrder()) {
            return -1;
        } elseif($a->getSortOrder()>$b->getSortOrder()) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getLabelVersion()
    {
        return Mage::getVersion();
    }

    public function getLabelTime()
    {
        return number_format(Varien_Profiler::fetch('mage','sum'),2).' s';
    }

    public function getLabelMemory()
    {
        return Mage::helper('wee_developertoolbar')->formatBytes(memory_get_usage(true));
    }

    public function getLabelDatabase()
    {
        $profiler = Mage::getSingleton('core/resource')->getConnection('core_write')->getProfiler();
        return $profiler->getTotalNumQueries();
    }

    /**
     *
     * Cut an paste from Hackathon_MageMonitoring_Helper_Data::tailFile
     * @see https://github.com/magento-hackathon/Hackathon_MageMonitoring/blob/master/app/code/community/Hackathon/MageMonitoring/Helper/Data.php
     *
     * tail -n in php, kindly lifted from https://gist.github.com/lorenzos/1711e81a9162320fde20
     *
     * @param string $filepath
     * @param int $lines
     * @param bool $adaptive use adaptive buffersize for seeking, if false use static buffersize of 4096
     *
     * @return string
     */
    function tailFile($filepath, $lines = 1, $adaptive = true) {
        // Open file
        $f = @fopen($filepath, "rb");
        if ($f === false) return false;

        // Sets buffer size
        if (!$adaptive) $buffer = 4096;
        else $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));

        // Jump to last character
        fseek($f, -1, SEEK_END);

        // Read it and adjust line number if necessary
        // (Otherwise the result would be wrong if file doesn't end with a blank line)
        if (fread($f, 1) != "\n") $lines -= 1;

        // Start reading
        $output = '';
        $chunk = '';

        // While we would like more
        while (ftell($f) > 0 && $lines >= 0) {
            // Figure out how far back we should jump
            $seek = min(ftell($f), $buffer);
            // Do the jump (backwards, relative to where we are)
            fseek($f, -$seek, SEEK_CUR);
            // Read a chunk and prepend it to our output
            $output = ($chunk = fread($f, $seek)) . $output;
            // Jump back to where we started reading
            fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
            // Decrease our line counter
            $lines -= substr_count($chunk, "\n");
        }

        // While we have too many lines
        // (Because of buffer size we might have read too many)
        while ($lines++ < 0) {
            // Find first newline and remove all text before that
            $output = substr($output, strpos($output, "\n") + 1);
        }
        // Close file and return
        fclose($f);
        return trim($output);
    }

}