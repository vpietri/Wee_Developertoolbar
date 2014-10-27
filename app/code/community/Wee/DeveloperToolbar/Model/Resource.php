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

class Wee_DeveloperToolbar_Model_Resource extends Mage_Core_Model_Resource
{
    /**
     * Create new connection adapter instance by connection type and config
     *
     * @param string $type the connection type
     * @param Mage_Core_Model_Config_Element|array $config the connection configuration
     * @return Varien_Db_Adapter_Interface|false
     */
    protected function _newConnection($type, $config)
    {
        $connection = parent::_newConnection($type, $config);

        if (Mage::helper('wee_developertoolbar')->isToolbarAccessAllowed(true)) {
            $connection->getProfiler()->setEnabled(true);
        }

        return $connection;
    }
}