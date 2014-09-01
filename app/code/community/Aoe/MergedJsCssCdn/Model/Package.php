<?php
/**
 * @author Dmytro Zavalkin <dmytro.zavalkin@aoemedia.de>
 */
class Aoe_MergedJsCssCdn_Model_Package extends Aoe_JsCssTstamp_Model_Package
{
    /**
     * @return Aoe_AmazonCdn_Helper_Data
     */
    protected function _getAoeAmazonCdnHelper()
    {
        return Mage::helper('aoe_amazoncdn');
    }

    /**
     * Generate CDN url for merged file of given $type.
     * Upload file to CDN if it doesn't exist there yet
     *
     * @param string $type
     * @param array $files
     * @param string $targetDir
     * @param string $targetFilename
     * @return string
     */
    protected function generateMergedUrl($type, array $files, $targetDir, $targetFilename)
    {
        $nativeUrl = parent::generateMergedUrl($type, $files, $targetDir, $targetFilename);

        $path = $targetDir . DS . $this->getProtocolSpecificTargetFileName($targetFilename);;

        $url = $this->_getAoeAmazonCdnHelper()->getCdnUrl($path);
        if (!$url && is_file($path)) {
            $url = $this->_getAoeAmazonCdnHelper()->storeInCdn($path);
            if ($url) {
                OnePica_ImageCdn_Helper_Data::log(sprintf('Stored merged %s file "%s" to cdn. Url "%s"', $type, $path, $url), Zend_Log::DEBUG);
            } else {
                $url = $nativeUrl;
                OnePica_ImageCdn_Helper_Data::log(sprintf('Can not store merged %s file "%s" to cdn.', $type, $path), Zend_Log::ERR);
            }
        }

        return $url;
    }

    /**
     * Remove all merged js/css files
     *
     * @return bool
     */
    public function cleanMergedJsCss()
    {
        $parentResult = parent::cleanMergedJsCss();
        $cdnResult    = $this->_getAoeAmazonCdnHelper()->clearCssJsCache();

        return $parentResult && $cdnResult;
    }
}
