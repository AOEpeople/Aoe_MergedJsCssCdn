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

        if ($this->_getAoeAmazonCdnHelper()->isConfigured()) {
            $logger = $this->_getAoeAmazonCdnHelper()->getLogger();

            $fileName = $targetDir . DS . $this->getProtocolSpecificTargetFileName($targetFilename);
            $url      = $nativeUrl;
            $cdnUrl   = $this->_getAoeAmazonCdnHelper()->getCdnAdapter()->getUrl($fileName);
            if ($this->_getAoeAmazonCdnHelper()->getCacheFacade()->get($fileName)) {
                $url = $cdnUrl;
            } elseif (is_file($fileName)) {
                if ($this->_getAoeAmazonCdnHelper()->getCdnAdapter()->save($fileName, $fileName)) {
                    $url = $cdnUrl;
                    $logger->log(sprintf('Stored merged %s file "%s" to cdn. Url "%s"', $type, $fileName, $cdnUrl),
                        Zend_Log::DEBUG
                    );
                } else {
                    $logger->log(sprintf('Can not store merged %s file "%s" to cdn.', $type, $fileName),
                        Zend_Log::ERR
                    );
                }
            }

            return $url;
        } else {
            return $nativeUrl;
        }
    }

    /**
     * Remove all merged js/css files
     *
     * @return bool
     */
    public function cleanMergedJsCss()
    {
        $parentResult = parent::cleanMergedJsCss();
        $cdnResult    = true;
        if ($this->_getAoeAmazonCdnHelper()->isConfigured()) {
            $cdnResult = $this->_getAoeAmazonCdnHelper()->getCdnAdapter()->clearCssJsCache();
        }

        return $parentResult && $cdnResult;
    }
}
