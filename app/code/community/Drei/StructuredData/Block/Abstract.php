<?php

abstract class Drei_StructuredData_Block_Abstract extends Mage_Core_Block_Text
{
    const CONTEXT = 'http://schema.org/';

    /**
     * @return string
     * @throws Mage_Core_Exception
     */
    abstract public function getSnippet();

    /**
     * @return string
     */
    public function getText()
    {
        try {
            return implode('', array(
                '<script type="application/ld+json">',
                $this->getSnippet(),
                '</script>'
            ));
        } catch (Mage_Core_Exception $e) {
            return '';
        } catch (Exception $e) {
            Mage::logException($e);

            return '';
        }
    }
}
