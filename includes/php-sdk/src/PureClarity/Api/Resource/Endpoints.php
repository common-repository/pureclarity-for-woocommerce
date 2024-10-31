<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Api\Resource;

use Exception;

/**
 * Class Endpoints
 *
 * Handles generation of Endpoint URLs for the provided region
 */
class Endpoints
{
    /**
     * Region data cache
     *
     * @var array[]
     */
    private $regions = [];

    /**
     * Default PureClarity ClientScript URL
     *
     * @var string
     */
    private $scriptUrl = '//pcs.pureclarity.net/';

    /**
     * Gets the PureClarity dashboard endpoint for the given region
     *
     * @param integer $region
     * @return string
     * @throws Exception
     */
    public function getDashboardEndpoint($region)
    {
        return $this->getApiUrl($region) . '/api/plugin/dashboard';
    }

    /**
     * Gets the PureClarity account validation endpoint for the given region
     *
     * @param integer $region
     * @return string
     * @throws Exception
     */
    public function getValidateAccountEndpoint($region)
    {
        return $this->getApiUrl($region) . '/api/plugin/validate-account';
    }

    /**
     * Gets the PureClarity delete endpoint for the given region
     *
     * @param integer $region
     * @return string
     * @throws Exception
     */
    public function getDeleteEndpoint($region)
    {
        return $this->getApiUrl($region) . '/api/plugin/delete';
    }

    /**
     * Gets the PureClarity feedback endpoint for the given region
     *
     * @param integer $region
     * @return string
     * @throws Exception
     */
    public function getFeedbackEndpoint($region)
    {
        return $this->getApiUrl($region) . '/api/plugin/feedback';
    }

    /**
     * Gets the PureClarity next steps completion endpoint for the given region
     *
     * @param integer $region
     * @return string
     * @throws Exception
     */
    public function getNextStepsCompleteEndpoint($region)
    {
        return $this->getApiUrl($region) . '/api/next-steps/complete';
    }

    /**
     * Gets the PureClarity delta endpoint for the given region
     *
     * @param integer $region
     * @return string
     * @throws Exception
     */
    public function getDeltaEndpoint($region)
    {
        return $this->getApiUrl($region) . '/api/productdelta';
    }

    /**
     * Gets the PureClarity add store endpoint for the given region
     *
     * @param integer $region
     * @return string
     * @throws Exception
     */
    public function getAddStoreEndpoint($region)
    {
        return $this->getApiUrl($region) . '/api/plugin/add-store';
    }

    /**
     * Gets the PureClarity signup request endpoint for the given region
     *
     * @param integer $region
     * @return string
     * @throws Exception
     */
    public function getSignupRequestEndpoint($region)
    {
        return $this->getApiUrl($region) . '/api/plugin/signuprequest';
    }

    /**
     * Gets the PureClarity signup tracking endpoint for the given region
     *
     * @param integer $region
     * @return string
     * @throws Exception
     */
    public function getSignupStatusEndpoint($region)
    {
        return $this->getApiUrl($region) . '/api/plugin/signupstatus';
    }

    /**
     * Gets the PureClarity clientscript URL
     *
     * @param string $accessKey
     *
     * @return string
     */
    public function getClientScriptUrl($accessKey)
    {
        $pureclarityScriptUrl = getenv('PURECLARITY_SCRIPT_URL');
        if (empty($pureclarityScriptUrl)) {
            $pureclarityScriptUrl = $this->scriptUrl;
        }

        $pureclarityScriptUrl .= $accessKey . '/cs.js';

        return $pureclarityScriptUrl;
    }

    /**
     * Gets the PureClarity SFTP endpoint for the given region
     *
     * @param integer $region
     * @return string
     * @throws Exception
     */
    public function getSftpEndpoint($region)
    {
        $regionData = $this->getRegion($region);

        $url = getenv('PURECLARITY_FEED_HOST');
        if (empty($url)) {
            $url = $regionData['endpoints']['sftp'];
        }

        $port = getenv('PURECLARITY_FEED_PORT');
        if (!empty($port)) {
            $url = $url . ':' . $port;
        }

        return $url . '/';
    }

    /**
     * Gets the base PureClarity API URL for the given region
     *
     * @param $region
     * @return string
     * @throws Exception
     */
    private function getApiUrl($region)
    {
        $regionData = $this->getRegion($region);
        $host = getenv('PURECLARITY_HOST');
        if (empty($host)) {
            $host = $regionData['endpoints']['api'];
        }

        return $host;
    }

    /**
     * Gets the Region information for the provided region
     *
     * @param integer $region
     * @return mixed[]
     * @throws Exception
     */
    private function getRegion($region)
    {
        if (!isset($this->regions[$region])) {
            $regions = new Regions();
            $regionData = $regions->getRegion($region);
            if (!$regionData) {
                throw new Exception('Invalid Region supplied');
            }
            $this->regions[$region] = $regionData;
        }

        return $this->regions[$region];
    }
}
