<?php
/**
 * Created by PhpStorm.
 * User: elmehdi
 * Date: 16/07/17
 * Time: 18:16
 */

namespace Amikar;
use Amikar\Exception\AmikarSDKException;

/**
 * Class AmikarResponseException
 * @package Amikar
 */
class AmikarResponseException extends AmikarSDKException
{

    /**
     * @var AmikarResponse The response that threw the exception.
     */
    protected $response;
    /**
     * @var array Decoded response.
     */
    protected $responseData;
    /**
     * Creates a AmikarResponseException.
     *
     * @param AmikarResponse     $response          The response that threw the exception.
     * @param AmikarSDKException $previousException The more detailed exception.
     */
    public function __construct(AmikarResponse $response, AmikarSDKException $previousException = null)
    {
        $this->response = $response;
        $this->responseData = $response->getDecodedBody();
        $errorMessage = $this->get('message', 'Unknown error from Graph.');
        $errorCode = $this->get('code', -1);
        parent::__construct($errorMessage, $errorCode, $previousException);
    }
    /**
     * A factory for creating the appropriate exception based on the response from Graph.
     *
     * @param AmikarResponse $response The response that threw the exception.
     *
     * @return AmikarResponseException
     */
    public static function create(AmikarResponse $response)
    {
        $data = $response->getDecodedBody();
        if (!isset($data['error']['code']) && isset($data['code'])) {
            $data = ['error' => $data];
        }
        $code = isset($data['error']['code']) ? $data['error']['code'] : null;
        $message = isset($data['error']['message']) ? $data['error']['message'] : 'Unknown error from Graph.';
        if (isset($data['error']['error_subcode'])) {
            switch ($data['error']['error_subcode']) {
                // Other authentication issues
                case 458:
                case 459:
                case 460:
                case 463:
                case 464:
                case 467:
                    return new static($response, new AmikarAuthenticationException($message, $code));
                // Video upload resumable error
                case 1363030:
                case 1363019:
                case 1363037:
                case 1363033:
                case 1363021:
                case 1363041:
                    return new static($response, new AmikarResumableUploadException($message, $code));
            }
        }
        switch ($code) {
            // Login status or token expired, revoked, or invalid
            case 100:
            case 102:
            case 190:
                return new static($response, new AmikarAuthenticationException($message, $code));
            // Server issue, possible downtime
            case 1:
            case 2:
                return new static($response, new AmikarServerException($message, $code));
            // API Throttling
            case 4:
            case 17:
            case 341:
                return new static($response, new AmikarThrottleException($message, $code));
            // Duplicate Post
            case 506:
                return new static($response, new AmikarClientException($message, $code));
        }
        // Missing Permissions
        if ($code == 10 || ($code >= 200 && $code <= 299)) {
            return new static($response, new AmikarAuthorizationException($message, $code));
        }
        // OAuth authentication error
        if (isset($data['error']['type']) && $data['error']['type'] === 'OAuthException') {
            return new static($response, new AmikarAuthenticationException($message, $code));
        }
        // All others
        return new static($response, new AmikarOtherException($message, $code));
    }
    /**
     * Checks isset and returns that or a default value.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    private function get($key, $default = null)
    {
        if (isset($this->responseData['error'][$key])) {
            return $this->responseData['error'][$key];
        }
        return $default;
    }
    /**
     * Returns the HTTP status code
     *
     * @return int
     */
    public function getHttpStatusCode()
    {
        return $this->response->getHttpStatusCode();
    }
    /**
     * Returns the sub-error code
     *
     * @return int
     */
    public function getSubErrorCode()
    {
        return $this->get('error_subcode', -1);
    }
    /**
     * Returns the error type
     *
     * @return string
     */
    public function getErrorType()
    {
        return $this->get('type', '');
    }
    /**
     * Returns the raw response used to create the exception.
     *
     * @return string
     */
    public function getRawResponse()
    {
        return $this->response->getBody();
    }
    /**
     * Returns the decoded response used to create the exception.
     *
     * @return array
     */
    public function getResponseData()
    {
        return $this->responseData;
    }
    /**
     * Returns the response entity used to create the exception.
     *
     * @return AmikarResponse
     */
    public function getResponse()
    {
        return $this->response;
    }
}