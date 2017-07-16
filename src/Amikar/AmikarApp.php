<?php


namespace Amikar;


use Amikar\Exception\AmikarSDKException;

class AmikarApp
{
    /** @var  string */
    private $clientId;
    /** @var  string */
    private $clientSecret;

    /**
     * AmikarApp constructor.
     * @param string $clientId
     * @param string $clientSecret
     * @throws AmikarSDKException
     */
    public function __construct($clientId, $clientSecret)
    {
        if(! is_string($clientId)){
            throw new AmikarSDKException('The "client_id" must be formatted as a string');
        }

        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @param string $clientSecret
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }

}