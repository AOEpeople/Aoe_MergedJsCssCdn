<?php
/**
 * @author Dmytro Zavalkin <dmytro.zavalkin@aoemedia.de>
 */
class Aoe_MergedJsCssCdn_Model_Package extends Aoe_JsCssTstamp_Model_Package
{
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

        /* @var $helper Aoe_Cdn_Helper_Data */
        $helper = Mage::helper('aoecdn');
        $path = $targetDir . DS . $this->getProtocolSpecificTargetFileName($targetFilename);;

        $url = $helper->getCdnUrl($path);
        if (!$url && is_file($path)) {
            $url = $helper->storeInCdn($path);
        }

        if ($url) {
            Mage::log(sprintf('Copied merged %s file "%s" to cdn. Url "%s"', $type, $path, $url), Zend_Log::DEBUG);
        } else {
            $url = $nativeUrl;
            Mage::log(sprintf('Did not copy merged %s file "%s" to cdn.', $type, $path), Zend_Log::ERR);
        }

        return $url;
    }
}
