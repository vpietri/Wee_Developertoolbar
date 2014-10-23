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

class Wee_DeveloperToolbar_Helper_Data extends Mage_Core_Helper_Abstract
{
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


    function isRequestAllowed() {
        $allowedIps = Mage::getStoreConfig('dev/restrict/allow_ips');
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

        return $allow;
    }

    function getToolbarConfig($name=false)
    {

        $path = 'default/developertoolbar/items';

        $config = array();
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

                    $config[$configKey] = new Varien_Object($itemLine);
                }
            }
        }


        return ($name && isset($config[$name])) ? $config[$name] : $config;
    }

    public function getLabelVersion()
    {
        return Mage::getVersion();
    }

    public function getLabelInfo()
    {

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














}