<?php
class Crius_ConfigDanish_Block_Adminhtml_Configdanish extends Mage_Adminhtml_Block_Widget
{
    /**
     * Class Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTitle('Danish Configuration');
    }

    /**
     * Retrieve the POST URL for the form
     *
     * @return string URL
     */
    public function getPostActionUrl()
    {
        return $this->getUrl('*/*/save');
    }

    /**
     * Get old product tax classes
     *
     * @return array
     */
    public function getProductTaxClasses()
    {
        return Mage::getSingleton('tax/class_source_product')->getAllOptions();
    }

    /**
     * Get new product tax classes (yet to be created)
     *
     * @return array
     */
    public function getNewProductTaxClasses()
    {
        return Mage::getSingleton('configdanish/source_tax_newProductTaxClass')->getAllOptions();
    }

    /**
     * @return int default new tax class (yet to be created)
     */
    public function getDefaultProductTaxClass()
    {
        return Mage::getSingleton('configdanish/source_tax_newProductTaxClass')->getDefaultOption();
    }
}
