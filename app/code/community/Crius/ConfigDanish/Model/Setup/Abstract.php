<?php
class Crius_ConfigDanish_Model_Setup_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * Get config.xml data
     *
     * @return array
     */
    public function getConfigData()
    {
        $configData = Mage::getSingleton('configdanish/config')
            ->getNode('default/configdanish')
            ->asArray();
        return $configData;
    }

    /**
     * Get config.xml data
     *
     * @param string      $node      xml node
     * @param string|null $childNode if set, child node of the first node
     *
     * @return array
     */
    protected function _getConfigNode($node, $childNode = null)
    {
        $configData = $this->getConfigData();
        if ($childNode) {
            return $configData[$node][$childNode];
        } else {
            return $configData[$node];
        }
    }

    /**
     * Get template content
     *
     * @param string $filename template file name
     *
     * @return string
     */
    public function getTemplateContent($filename)
    {
        return file_get_contents(Mage::getBaseDir() . DS . $filename);
    }

    /**
     * Load a model by attribute code
     *
     * @param Mage_Core_Model_Abstract $model
     * @param string $attributeCode
     * @param string $value
     * @return Mage_Core_Model_Abstract
     */
    protected function _loadExistingModel($model, $attributeCode, $value)
    {
        foreach ($model->getCollection() as $singleModel) {
            if ($singleModel->getData($attributeCode) == $value) {
                $model->load($singleModel->getId());
                return $model;
            }
        }
        return $model;
    }
}
