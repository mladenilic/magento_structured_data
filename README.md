# Magento 1.x Google Structured Data Module

Simple, no configuration module that adds relevant [structured data](https://developers.google.com/search/docs/guides/intro-structured-data)
to a Magento shop. All data is outputted in JSON-LD format.

Out of the box, module adds following data:
1. [Product](https://developers.google.com/search/docs/data-types/product) data to single product page 
2. [Corporate contact](https://developers.google.com/search/docs/data-types/corporate-contact) and [Logo](https://developers.google.com/search/docs/data-types/logo) data to CMS index page (Homepage)

## Built for developers

Blocks dispatch events before rendering structured data, allowing to hooking in and altering it.
Idea is to allow developer to have full control over outputted data without having to edit module files.

Module dispatches following events:
1. `product_structured_data_prepare` – dispatcher when rendering product data
2. `organization_structured_data_prepare` – dispatcher when rendering organization data  

Example of how to implement event observer:

_app/code/local/Group/Module/etc/config.xml_
```xml
<?xml version="1.0"?>
<config>
    ...
    <global>
        ...
        
        <events>
            <product_structured_data_prepare>
                <observers>
                    <group_module>
                        <class>group_module/observer</class>
                        <method>productStructuredDataPrepare</method>
                    </group_module>
                </observers>
            </product_structured_data_prepare>
        </events>
    </global>
</config>
```

_app/code/local/Group/Module/Model/Observer.php_
```php
<?php

class Group_Module_Model_Observer
{
    public function productStructuredDataPrepare(Varien_Event_Observer $observer)
    {
        /** @var Drei_StructuredData_Block_Product $block */
        $block = $observer->getEvent()->getBlock();
        
         /** @var Mage_Catalog_Model_Product $product */
        $product = $observer->getEvent()->getProduct();
        
        /** @var Varien_Object $transport */
        $transport = $observer->getEvent()->getTransport();
        
        /*
         * Adding or updating data withing $transport object 
         * will affect the final output
         * 
         * For example: set description property to product SKU, 
         * instead of short description that's used by default  
         */
        $transport->setDescription($product->getSku());
    }
}
```
