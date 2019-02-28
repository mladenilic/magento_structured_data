<?php

class Drei_StructuredData_Block_Product extends Drei_StructuredData_Block_Abstract
{
    const TYPE = 'Product';
    const OFFER_TYPE = 'Offer';
    const AGGREGATE_RATING_TYPE = 'AggregateRating';

    const AVAILABILITY_IN_STOCK = 'http://schema.org/InStock';
    const AVAILABILITY_OUT_OF_STOCK = 'http://schema.org/OutOfStock';

    /**
     * {@inheritdoc}
     */
    public function getSnippet()
    {
        $data = array(
            '@context' => self::CONTEXT,
            '@type' => self::TYPE,
            'name' => $this->_getProduct()->getName(),
            'image' => $this->_getImages(),
            'sku' => $this->_getProduct()->getSku(),
            'description' => $this->_getProduct()->getData('short_description'),
            'offers' => array(
                '@type' => self::OFFER_TYPE,
                'priceCurrency' => Mage::app()->getStore()->getCurrentCurrencyCode(),
                'price' => $this->_getProduct()->getPrice(),
                'availability' => $this->_getAvailability(),
                'url' => $this->escapeUrl($this->_getProduct()->getProductUrl())
            )
        );

        if ($count = $this->_getReviewCount()) {
            $data['aggregateRating'] = array(
                '@type' => self::AGGREGATE_RATING_TYPE,
                'ratingValue' => $this->_getReviewRating(),
                'reviewCount' => $count,
            );
        }

        $transport = new Varien_Object($data);
        Mage::dispatchEvent('product_structured_data_prepare', array(
            'block' => $this,
            'transport' => $transport
        ));

        return Mage::helper('core')->jsonEncode($transport->getData());
    }

    /**
     * @return Mage_Catalog_Model_Product
     * @throws Mage_Core_Exception
     */
    protected function _getProduct()
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::registry('current_product');
        if (!$product) {
            Mage::throwException('Product not registered');
        }

        $product->getRatingSummary();

        return $product;
    }

    /**
     * @return array
     * @throws Mage_Core_Exception
     */
    protected function _getImages()
    {
        return array_filter(array_map(function ($image) {
            $url = $this->_getGalleryImageUrl($image);
            if (null === $url) {
                return null;
            }

            return $this->escapeUrl($url);
        }, $this->_getProduct()->getMediaGalleryImages()->toArray()['items']));
    }

    /**
     * @return float
     * @throws Mage_Core_Exception
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function _getReviewRating()
    {
        if (!$this->_getProduct()->getRatingSummary()) {
            Mage::getModel('review/review')->getEntitySummary($this->_getProduct(), Mage::app()->getStore()->getId());
        }

        return (int) $this->_getProduct()->getRatingSummary()->getRatingSummary() / 10;
    }

    /**
     * @return int
     * @throws Mage_Core_Exception
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function _getReviewCount()
    {
        if (!$this->_getProduct()->getRatingSummary()) {
            Mage::getModel('review/review')->getEntitySummary($this->_getProduct(), Mage::app()->getStore()->getId());
        }

        return $this->_getProduct()->getRatingSummary()->getReviewsCount();
    }

    /**
     * @param array $image
     * @return null|string
     * @throws Mage_Core_Exception
     */
    protected function _getGalleryImageUrl($image)
    {
        if ($image) {
            /** @var Mage_Catalog_Helper_Image $helper */
            $helper = $this->helper('catalog/image')
                ->init($this->_getProduct(), 'image', $image['file'])
                ->keepFrame(false);

            $size = Mage::getStoreConfig(Mage_Catalog_Helper_Image::XML_NODE_PRODUCT_BASE_IMAGE_WIDTH);
            if (is_numeric($size)) {
                $helper->constrainOnly(true)->resize($size);
            }

            return (string)$helper;
        }

        return null;
    }

    /**
     * @return string
     * @throws Mage_Core_Exception
     */
    protected function _getAvailability()
    {
        return $this->_getProduct()->isAvailable() ?
            self::AVAILABILITY_IN_STOCK :
            self::AVAILABILITY_OUT_OF_STOCK;
    }
}
