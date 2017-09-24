<?php


class Wee_DeveloperToolbar_Model_Pro_Resource_Type_Db_Pdo_Mysql extends Mage_Core_Model_Resource_Type_Db_Pdo_Mysql
{

    /**
     * Create and return DB adapter object instance
     *
     * @param array $configArr Connection config
     * @return Varien_Db_Adapter_Pdo_Mysql
     */
    protected function _getDbAdapterInstance($configArr)
    {
        $configArr['profiler'] = array('enabled'=>true, 'class'=>'Wee_DeveloperToolbar_Model_Pro_Db_Profiler');
        return parent::_getDbAdapterInstance($configArr);
    }

}