<?php
class Crius_ConfigDanish_Model_Setup_Attributes extends Crius_ConfigDanish_Model_Setup_Abstract
{
    /**
     * @var Mage_Eav_Model_Entity_Setup
     */
    protected $_setup;

    /**
     * @var Varien_Db_Adapter_Interface
     */
    protected $_connection;

    /**
     * Setup setup class and connection
     */
    public function __construct()
    {
        $this->_setup = Mage::getModel('eav/entity_setup', 'core_setup');
        $this->_connection = $this->_setup->getConnection();
    }
    
    /**
     * Setup attribute names
     *
     * @return void
     */
    public function setup()
    {
        // Update product attribute labels
        foreach ($this->_getProductAttributeLabels() as $attrCode => $attrLabel) {
            $this->_updateAttributeLabel('catalog_product', $attrCode, $attrLabel);
        }
        
        // Update category attribute labels
        foreach ($this->_getCategoryAttributeLabels() as $attrCode => $attrLabel) {
            $this->_updateAttributeLabel('catalog_category', $attrCode, $attrLabel);
        }
    }
    
    /**
     * Get product attribute labels from config file
     *
     * @return array
     */
    protected function _getProductAttributeLabels()
    {
        return $this->_getConfigNode('product_attribute_labels', 'default');
    }
    
    /**
     * Get category attribute labels from config file
     *
     * @return array
     */
    protected function _getCategoryAttributeLabels()
    {
        return $this->_getConfigNode('category_attribute_labels', 'default');
    }
    
    /**
     * Save attribute label
     *
     * @param string $entityTypeId
     * @param string $attributeCode
     * @param string $label
     */
    protected function _updateAttributeLabel($entityTypeId, $attributeCode, $label) {
        $setup = $this->_getSetup();
		if ($id = $setup->getAttribute($entityTypeId, $attributeCode, 'attribute_id')) {
			$setup->updateAttribute($entityTypeId, $id, 'frontend_label', $label);
		}
	}
    
    /**
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getConnection()
    {
        return $this->_connection;
    }

    /**
     * @return Mage_Eav_Model_Entity_Setup
     */
    protected function _getSetup()
    {
        return $this->_setup;
    }
}
