<?php

namespace Amikar;

use Amikar\Exception\AmikarSDKException;
use Amikar\Http\RequestBodyJsonEncoded;
use Amikar\Http\RequestBodyUrlEncoded;
use Amikar\Http\RequestBodyMultipart;

/**
 * Class AmikarRequest
 * @package Amikar
 */
class AmikarRequest
{

    /**
     * @var AmikarApp The Facebook app entity.
     */
    protected $app;
    /**
     * @var string|null The access token to use for this request.
     */
    protected $accessToken;
    /**
     * @var string The HTTP method for this request.
     */
    protected $method;
    /**
     * @var string The Graph endpoint for this request.
     */
    protected $endpoint;
    /**
     * @var array The headers to send with this request.
     */
    protected $headers = [];
    /**
     * @var array The parameters to send with this request.
     */
    protected $params = [];
    /**
     * @var array The files to send with this request.
     */
    protected $files = [];
    /**
     * @var string ETag to send with this request.
     */
    protected $eTag;
    /**
     * @var string Graph version to use for this request.
     */
    protected $apiVersion;

    /**
     * Creates a new Request entity.
     *
     * @param AmikarApp|null $app
     * @param AccessToken|string|null $accessToken
     * @param string|null $method
     * @param string|null $endpoint
     * @param array|null $params
     * @param string|null $eTag
     * @param string|null $apiVersion
     */
    public function __construct(AmikarApp $app = null, $accessToken = null, $method = null, $endpoint = null, array $params = [], $eTag = null, $apiVersion = null)
    {
        $this->setApp($app);
        $this->setAccessToken($accessToken);
        $this->setMethod($method);
        $this->setEndpoint($endpoint);
        $this->setParams($params);
        $this->setETag($eTag);
        $this->apiVersion = $apiVersion ?: Amikar::DEFAULT_API_VERSION;
    }


    /**
     * Set the access token for this request.
     *
     * @param AccessToken|string|null
     *
     * @return AmikarRequest
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        if ($accessToken instanceof AccessToken) {
            $this->accessToken = $accessToken->getValue();
        }
        return $this;
    }

    /**
     * Sets the access token with one harvested from a URL or POST params.
     *
     * @param string $accessToken The access token.
     *
     * @return AmikarRequest
     *
     * @throws AmikarSDKException
     */
    public function setAccessTokenFromParams($accessToken)
    {
        $existingAccessToken = $this->getAccessToken();
        if (!$existingAccessToken) {
            $this->setAccessToken($accessToken);
        } elseif ($accessToken !== $existingAccessToken) {
            throw new AmikarSDKException('Access token mismatch. The access token provided in the FacebookRequest and the one provided in the URL or POST params do not match.');
        }
        return $this;
    }

    /**
     * Return the access token for this request.
     *
     * @return string|null
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }
    /**
     * Return the access token for this request as an AccessToken entity.
     *
     * @return AccessToken|null
     */
    public function getAccessTokenEntity()
    {
        return $this->accessToken ? new AccessToken($this->accessToken) : null;
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
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @param string $endpoint
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    /**
     * adds header entry to the request headers
     *
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param array $files
     */
    public function setFiles($files)
    {
        $this->files = $files;
    }

    /**
     * @return string
     */
    public function getETag()
    {
        return $this->eTag;
    }

    /**
     * @param string $eTag
     */
    public function setETag($eTag)
    {
        $this->eTag = $eTag;
    }

    /**
     * @return string
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     * @param string $apiVersion
     */
    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;
    }

    /**
     * Validate that the HTTP method is set.
     *
     * @throws AmikarSDKException
     */
    public function validateMethod()
    {
        if (!$this->method) {
            throw new AmikarSDKException('HTTP method not specified.');
        }
        if (!in_array($this->method, ['GET', 'POST', 'DELETE', 'PUT'])) {
            throw new AmikarSDKException('Invalid HTTP method specified.');
        }
    }

    /**
     * Let's us know if there is a file upload with this request.
     *
     * @return boolean
     */
    public function containsFileUploads()
    {
        return !empty($this->files);
    }

    /**
     * Returns the body of the request as multipart/form-data.
     *
     * @return RequestBodyMultipart
     */
    public function getMultipartBody()
    {
        $params = $this->getPostParams();
        return new RequestBodyMultipart($params, $this->files);
    }

    /**
     * Returns the body of the request as URL-encoded.
     *
     * @return RequestBodyUrlEncoded
     */
    public function getUrlEncodedBody()
    {
        $params = $this->getPostParams();
        return new RequestBodyUrlEncoded($params);
    }

    /**
     * Only return params on POST requests.
     *
     * @return array
     */
    public function getPostParams()
    {
        if ($this->getMethod() === 'POST') {
            return $this->getParams();
        }
        return [];
    }

}
