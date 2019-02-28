<?php

class Drei_StructuredData_Block_Organization extends Drei_StructuredData_Block_Abstract
{
    const TYPE = 'Organization';
    const CONTACT_POINT_TYPE = 'ContactPoint';

    /**
     * {@inheritdoc}
     */
    public function getSnippet()
    {
        $data = array(
            '@context' => self::CONTEXT,
            '@type' => self::TYPE,
            'url' => Mage::app()->getStore()->getBaseUrl(),
            'logo' => $this->escapeUrl($this->_getLogoUrl()),
        );

        if ($phone = $this->_getPhone()) {
            $data['contactPoint'] = array(
                '@type' => self::CONTACT_POINT_TYPE,
                'telephone' => $phone
            );
        }

        $transport = new Varien_Object($data);
        Mage::dispatchEvent('organization_structured_data_prepare', array(
            'block' => $this,
            'transport' => $transport
        ));

        return Mage::helper('core')->jsonEncode($transport);
    }

    /**
     * @return string
     */
    protected function _getLogoUrl()
    {
        if (empty($this->_data['logo_src'])) {
            $this->_data['logo_src'] = Mage::getStoreConfig('design/header/logo_src');
        }

        return $this->getSkinUrl($this->_data['logo_src']);
    }

    /**
     * @return string
     */
    protected function _getPhone()
    {
        return Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_PHONE);
    }
}
