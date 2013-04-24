<?php
class Crius_ConfigDanish_Model_Setup_Addressforms extends Crius_ConfigDanish_Model_Setup_Abstract
{
    /**
     * Setup address form templates
     *
     * @return void
     */
    public function setup()
    {
        $setup = Mage::getModel('eav/entity_setup', 'core_setup');
        $setup->setConfigData('configdanish/addressforms/enabled', 1);
    }
}
