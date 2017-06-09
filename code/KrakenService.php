<?php

/**
 * the kraken service handles authenication to the kraken
 * api and provides functions for calling the api
 * @author Ryan
 */
class KrakenService extends Object
{

    private static $enabled = false;
    private static $api_key = null;
    private static $api_secret = null;
    private static $lossy = true;
    private static $wait = true;
    private static $process_on_upload = false;

    /**
     *
     * @var type
     */
    protected $krakenConnection = null;


    /**
     * establish connection to the Kraken API
     * @return Kraken
     */
    public function getConnection()
    {
        if ($this->krakenConnection) {
            return $this->krakenConnection;
        }

        //establish connection if api keys are set
        if ($this->checkApiKeys()) {
            $kraken = new Kraken($this->config()->api_key, $this->config()->api_secret);

            $this->krakenConnection = $kraken;

            return $this->krakenConnection;
        }
    }

    /**
     * optimize an image
     * @param String $file
     * @return Array
     */
    public function optimizeImage($file)
    {
        $kraken = $this->getConnection();

        if ($kraken) {

            $params = array(
                "file"  => $file,
                "lossy" => $this->getLossy(),
                //"dev"   => $this->getDev()
            );

            if ($this->getWait()) {
                $params['wait'] = true;
            }

            set_time_limit(400);
            $data = $kraken->upload($params);

            return $data;
        }
    }

    /**
     * Uses cURL to fetch an optimized image from Kraken
     * @param String $krakedUrl
     * @return JSON
     */
    public function getOptimizedImage($krakedUrl)
    {
        $ch = curl_init($krakedUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.85 Safari/537.36');

        $result = curl_exec($ch);

        if ($result) {
            return $result;
        }
    }

    /**
     * return wait option
     * @return Boolean
     */
    public function getWait()
    {
        return $this->config()->wait;
    }

    /**
     * return the lossy option
     * @return type
     */
    public function getLossy()
    {
        return $this->config()->lossy;
    }


    /**
     * check if the API key and API secret have been
     * configured
     * @return boolean
     */
    private function checkApiKeys()
    {
        if ($this->config()->api_key === null || $this->config()->api_secret === null) {
            return user_error('The Kraken API Key and API Secret have not been configured in SiteConfig.', E_USER_ERROR);
        } else {
            return true;
        }
    }

    /**
     * check api status
     * @return boolean
     */
    public function getAPIStatus()
    {
        if ($this->checkApiKeys() ) {
            $kraken = new Kraken($this->config()->api_key, $this->config()->api_secret);
            $status = $kraken->status();
            return $status;
        }

        return false;
    }

}