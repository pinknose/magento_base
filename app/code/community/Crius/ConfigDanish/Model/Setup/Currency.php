<?php
class Crius_ConfigDanish_Model_Setup_Currency extends Crius_ConfigDanish_Model_Setup_Abstract
{
    /**
     * Setup currency symbol
     *
     * @return void
     */
    public function setup()
    {
        $symbolsModel = Mage::getModel('currencysymbol/system_currencysymbol');
        $symbolsArray = array();
        $symbolsConfig = Mage::getStoreConfig('currency/options/customsymbol');
        if ($symbolsConfig) {
            $symbolsArray = unserialize($symbolsConfig);
        }
        $symbolsArray['DKK'] = 'DKK ';
        $symbolsModel->setCurrencySymbolsData($symbolsArray);
    }
}
