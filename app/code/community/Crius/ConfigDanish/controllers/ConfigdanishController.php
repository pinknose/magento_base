<?php
class Crius_ConfigDanish_ConfigdanishController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Basic action: setup form
     *
     * @return void
     */
    public function indexAction()
    {
        $helper = Mage::helper('configdanish');
        $this->loadLayout()
            ->_setActiveMenu('system/configdanish/setup')
            ->_addBreadcrumb($helper->__('Danish Configuration'), $helper->__('Danish Configuration'))
            ->renderLayout();
    }

    /**
     * Basic action: setup save action
     *
     * @return void
     */
    public function saveAction()
    {
        if ($this->getRequest()->isPost()) {
            try {
                if ($this->getRequest()->getParam('tax') == 1) {
                    Mage::getSingleton('configdanish/setup_tax')->setup();
                    $this->_getSession()->addSuccess($this->__('Danish Config: Tax Settings have been created.'));
                    Mage::log($this->__('Danish Config: Tax Settings have been created.'));

                    $this->_updateProductTaxClasses();
                    $this->_getSession()->addSuccess($this->__('Danish Config: Product Tax Classes have been updated.'));
                    Mage::log($this->__('Danish Config: Product Tax Classes have been updated.'));
                }
                
                if ($this->getRequest()->getParam('euvat') == 1) {
                    Mage::getSingleton('configdanish/setup_euvat')->setup();
                    $this->_getSession()->addSuccess($this->__('Danish Config: EU VAT Settings have been created.'));
                    Mage::log($this->__('Danish Config: EU VAT Settings have been created.'));
                }
                
                if ($this->getRequest()->getParam('currency') == 1) {
                    Mage::getSingleton('configdanish/setup_currency')->setup();
                    $this->_getSession()->addSuccess($this->__('Danish Config: Currency symbol have been set.'));
                    Mage::log($this->__('Danish Config: Currency symbol have been set.'));
                }
                
                if ($this->getRequest()->getParam('attributes') == 1) {
                    Mage::getSingleton('configdanish/setup_attributes')->setup();
                    $this->_getSession()->addSuccess($this->__('Danish Config: Attribute labels have been translated.'));
                    Mage::log($this->__('Danish Config: Attribute labels have been translated.'));
                }
                
                if ($this->getRequest()->getParam('addressforms') == 1) {
                    Mage::getSingleton('configdanish/setup_addressforms')->setup();
                    $this->_getSession()->addSuccess($this->__('Danish Config: Address forms have been enabled.'));
                    Mage::log($this->__('Danish Config: Address forms have been enabled.'));
                }
                
                // Set a config flag to indicate that the setup has been initialized.
                Mage::getModel('eav/entity_setup', 'core_setup')->setConfigData('configdanish/is_initialized', '1');

            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*');
    }

    protected function _updateProductTaxClasses()
    {
        $taxClasses = $this->getRequest()->getParam('product_tax_class_target');
        foreach ($taxClasses as $source => $target) {
            if ($target = intval($target)) {

                Mage::getSingleton('configdanish/setup_tax')->updateProductTaxClasses($source, $target);
            }
        }
    }
}
