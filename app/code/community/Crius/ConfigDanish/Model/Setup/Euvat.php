<?php
class Crius_ConfigDanish_Model_Setup_Euvat extends Crius_ConfigDanish_Model_Setup_Abstract
{
    /**
     * Setup Tax setting
     *
     * @return void
     */
    public function setup()
    {
        // Create customer group for EU companies
        $eub2bGroup = Mage::getModel('customer/group')
            ->setCustomerGroupCode('EU B2B')
            ->setTaxClassId(5)
            ->save();
        
        // modify config data
        $this->_updateConfigData(Mage::getStoreConfig('customer/create_account/default_group'), $eub2bGroup->getId());
    }
    
    /**
     * Update configuration settings
     *
     * @param int $defaultGroupId
     * @param int $eub2bGroupId
     * @return void
     */
    protected function _updateConfigData($defaultGroupId, $eub2bGroupId)
    {
        // Update config fields
        $setup = Mage::getModel('eav/entity_setup', 'core_setup');
        foreach ($this->_getConfigEuvatConfig() as $key => $value) {
            $setup->setConfigData(str_replace('__', '/', $key), $value);
        }
        
        // Customer groups for VAT validation
        $setup->setConfigData('customer/create_account/viv_domestic_group', $defaultGroupId);
        $setup->setConfigData('customer/create_account/viv_intra_union_group', $eub2bGroupId);
        $setup->setConfigData('customer/create_account/viv_invalid_group', $defaultGroupId);
        $setup->setConfigData('customer/create_account/viv_error_group', $defaultGroupId);
    }
    
    /**
     * Get tax calculations from config file
     *
     * @return array
     */
    protected function _getConfigEuvatConfig()
    {
        return $this->_getConfigNode('euvat_config', 'default');
    }
}
