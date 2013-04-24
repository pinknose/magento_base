<?php
class Crius_ConfigDanish_Model_Config extends Varien_Simplexml_Config
{
    const CACHE_ID  = 'configdanish_config';
    const CACHE_TAG = 'configdanish_config';
     
    public function __construct($sourceData=null)
    {
        $this->setCacheId(self::CACHE_ID);
        $this->setCacheTags(array(self::CACHE_TAG));
        parent::__construct($sourceData);
        $this->_loadConfig();
    }

    /**
     * Merge default config with config from additional xml files
     *
     * @return Crius_ConfigDanish_Model_Config
     */
    protected function _loadConfig()
    {
        if (Mage::app()->useCache(self::CACHE_ID)) {
            if ($this->loadCache()) {
                return $this;
            }
        }
     
        $mergeConfig = Mage::getModel('core/config_base');
        $config = Mage::getConfig();
     
        // Load additional config files
        $configFile = $config->getModuleDir('etc', 'Crius_ConfigDanish') . DS . 'tax.xml';
        if (file_exists($configFile)) {
            if ($mergeConfig->loadFile($configFile)) {
                $config->extend($mergeConfig, true);
            }
        }
        
        $configFile = $config->getModuleDir('etc', 'Crius_ConfigDanish') . DS . 'euvat.xml';
        if (file_exists($configFile)) {
            if ($mergeConfig->loadFile($configFile)) {
                $config->extend($mergeConfig, true);
            }
        }
        
        $configFile = $config->getModuleDir('etc', 'Crius_ConfigDanish') . DS . 'attributes.xml';
        if (file_exists($configFile)) {
            if ($mergeConfig->loadFile($configFile)) {
                $config->extend($mergeConfig, true);
            }
        }
        
        $this->setXml($config->getNode());
     
        if (Mage::app()->useCache(self::CACHE_ID)) {
            $this->saveCache();
        }
        return $this;
    }
}
     
