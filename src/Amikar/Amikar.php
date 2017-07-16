<?php
/**
 * Created by PhpStorm.
 * User: elmehdi
 * Date: 16/07/17
 * Time: 15:16
 */

namespace Amikar;


use Amikar\Exception\AmikarSDKException;

class Amikar
{

    /**
     * @const string Version number of the Amikar PHP SDK.
     */
    const VERSION = '1.0';
    /**
     * @const string Default Amikar API version for requests.
     */
    const DEFAULT_API_VERSION = 'v1.0';
    /**
     * @const string The name of the environment variable that contains the app ID.
     */
    const APP_ID_ENV_NAME = 'AMIKAR_APP_ID';
    /**
     * @const string The name of the environment variable that contains the app secret.
     */
    const APP_SECRET_ENV_NAME = 'AMIKAR_APP_SECRET';
    /**
     * @const string The Api base uri.
     */
    const API_BASE_URI = 'http://localhost:8081';
    /**
     * @const string The Api port
     */
    const API_DEFAULT_PORT = '8081';

    /**
     * @var AmikarApp The AmikarApp entity.
     */
    protected $app;
    /**
     * @var AmikarClient The Amikar client service.
     */
    protected $client;
    /**
     * @var OAuth2Client The OAuth 2.0 client service.
     */
    protected $oAuth2Client;

    /**
     * @var AmikarResponse|null Stores the last request made to Rest Api.
     */
    protected $lastResponse;

    /**
     * @var AccessToken|null The default access token to use with requests.
     */
    protected $defaultAccessToken;

    /**
     * @var string|null The default Api version we want to use.
     */
    protected $defaultApiVersion;

    /**
     * Amikar constructor.
     * @param array $config
     * @throws AmikarSDKException
     */
    public function __construct(array $config = [])
    {
        $config = array_merge([
            'client_id' => getenv(static::APP_ID_ENV_NAME),
            'client_secret' => getenv(static::APP_SECRET_ENV_NAME),
            'base_url' => static::API_BASE_URI.':'.static::API_DEFAULT_PORT,
            'default_api_version' => static::DEFAULT_API_VERSION,
            'http_client_handler' => null
        ], $config);

        if (!$config['client_id']) {
            throw new AmikarSDKException('Required "client_id" key not supplied in config and could not find fallback environment variable "' . static::APP_ID_ENV_NAME . '"');
        }
        if (!$config['client_secret']) {
            throw new AmikarSDKException('Required "client_secret" key not supplied in config and could not find fallback environment variable "' . static::APP_SECRET_ENV_NAME . '"');
        }

        if (isset($config['default_access_token'])) {
            $this->setDefaultAccessToken($config['default_access_token']);
        }

        $this->app = new AmikarApp($config['client_id'], $config['client_secret']);

        $this->client = new AmikarClient($config);

    }

    /**
     * Sends a GET request to Graph and returns the result.
     *
     * @param string                  $endpoint
     * @param AccessToken|string|null $accessToken
     * @param string|null             $eTag
     * @param string|null             $apiVersion
     *
     * @return AmikarResponse
     *
     * @throws AmikarSDKException
     */
    public function get($endpoint, $accessToken = null, $eTag = null, $apiVersion = null)
    {
        return $this->sendRequest(
            'GET',
            $endpoint,
            $params = [],
            $accessToken,
            $eTag,
            $apiVersion
        );
    }
    /**
     * Sends a POST request to Graph and returns the result.
     *
     * @param string                  $endpoint
     * @param array                   $params
     * @param AccessToken|string|null $accessToken
     * @param string|null             $eTag
     * @param string|null             $apiVersion
     *
     * @return AmikarResponse
     *
     * @throws AmikarSDKException
     */
    public function post($endpoint, array $params = [], $accessToken = null, $eTag = null, $apiVersion = null)
    {
        return $this->sendRequest(
            'POST',
            $endpoint,
            $params,
            $accessToken,
            $eTag,
            $apiVersion
        );
    }
    /**
     * Sends a DELETE request to Graph and returns the result.
     *
     * @param string                  $endpoint
     * @param array                   $params
     * @param AccessToken|string|null $accessToken
     * @param string|null             $eTag
     * @param string|null             $apiVersion
     *
     * @return AmikarResponse
     *
     * @throws AmikarSDKException
     */
    public function delete($endpoint, array $params = [], $accessToken = null, $eTag = null, $apiVersion = null)
    {
        return $this->sendRequest(
            'DELETE',
            $endpoint,
            $params,
            $accessToken,
            $eTag,
            $apiVersion
        );
    }

    /**
     * Sends a request to Graph and returns the result.
     *
     * @param string                  $method
     * @param string                  $endpoint
     * @param array                   $params
     * @param AccessToken|string|null $accessToken
     * @param string|null             $eTag
     * @param string|null             $apiVersion
     *
     * @return AmikarResponse
     *
     * @throws AmikarSDKException
     */
    public function sendRequest($method, $endpoint, array $params = [], $accessToken = null, $eTag = null, $apiVersion = null)
    {
        $accessToken = $accessToken ?: $this->defaultAccessToken;
        $version = $apiVersion ?: $this->defaultApiVersion;
        $request = $this->request($method, $endpoint, $params, $accessToken, $eTag, $version);
        return $this->lastResponse = $this->client->sendRequest($request);
    }

    /**
     * Instantiates a new AmikarRequest entity.
     *
     * @param string                  $method
     * @param string                  $endpoint
     * @param array                   $params
     * @param AccessToken|string|null $accessToken
     * @param string|null             $eTag
     * @param string|null             $apiVersion
     *
     * @return AmikarRequest
     *
     * @throws AmikarSDKException
     */
    public function request($method, $endpoint, array $params = [], $accessToken = null, $eTag = null, $apiVersion = null)
    {
        $accessToken = $accessToken ?: $this->defaultAccessToken;
        $apiVersion = $apiVersion ?: $this->defaultApiVersion;
        return new AmikarRequest(
            $this->app,
            $accessToken,
            $method,
            $endpoint,
            $params,
            $eTag,
            $apiVersion
        );
    }

    /**
     * @return AmikarApp
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param AmikarApp $app
     */
    public function setApp($app)
    {
        $this->app = $app;
    }

    /**
     * @return AmikarClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param AmikarClient $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @return OAuth2Client
     */
    public function getOAuth2Client()
    {
        return $this->oAuth2Client;
    }

    /**
     * @param OAuth2Client $oAuth2Client
     */
    public function setOAuth2Client($oAuth2Client)
    {
        $this->oAuth2Client = $oAuth2Client;
    }

    /**
     * @return AmikarResponse|null
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * @param AmikarResponse|null $lastResponse
     */
    public function setLastResponse($lastResponse)
    {
        $this->lastResponse = $lastResponse;
    }

    /**
     * @return AccessToken|null
     */
    public function getDefaultAccessToken()
    {
        return $this->defaultAccessToken;
    }

    /**
     * @param AccessToken|null $defaultAccessToken
     */
    public function setDefaultAccessToken($defaultAccessToken)
    {
        $this->defaultAccessToken = $defaultAccessToken;
    }

    /**
     * @return null|string
     */
    public function getDefaultApiVersion()
    {
        return $this->defaultApiVersion;
    }

    /**
     * @param null|string $defaultApiVersion
     */
    public function setDefaultApiVersion($defaultApiVersion)
    {
        $this->defaultApiVersion = $defaultApiVersion;
    }

}