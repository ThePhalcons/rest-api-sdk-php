<?php
/**
 * *
 *  * Copyright 2017 Amikar, Inc.
 *  *
 *  * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 *  * use, copy, modify, and distribute this software in source code or binary
 *  * form for use in connection with the web services and APIs provided by
 *  * Facebook.
 *  *
 *  * As with any software that integrates with the Amikar platform, your use
 *  * of this software is subject to the Amikar Developer Principles and
 *  * Policies [http://developers.amikar.com/policy]. This copyright notice
 *  * shall be included in all copies or substantial portions of the software.
 *  *
 *  * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 *  * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 *  * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 *  * DEALINGS IN THE SOFTWARE.
 *  *
 *
 */

namespace Amikar;
use Amikar\HttpClient\AmikarGuzzleHttpHTTPClient;




/**
 * Class AmikarClient
 * @package Amikar
 */
class AmikarClient implements AmikarClientInterface
{
    /**
     * @const int The timeout in seconds for a normal request.
     */
    const DEFAULT_REQUEST_TIMEOUT = 60;

    /**
     * @const int The timeout in seconds for a request that contains file uploads.
     */
    const DEFAULT_FILE_UPLOAD_REQUEST_TIMEOUT = 3600;

    /**
     * @var int The number of calls that have been made to Graph.
     */
    private static $requestCount = 0;

    /** @var  AmikarGuzzleHttpHTTPClient $httpClientHandler */
    private $httpClientHandler;

    /**
     * AmikarClient constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->httpClientHandler = new AmikarGuzzleHttpHTTPClient($config);
    }

    /**
     * Prepares the request for sending to the client handler.
     *
     * @param AmikarRequest $request
     *
     * @return array
     */
    public function prepareRequestMessage(AmikarRequest $request)
    {
        if ($request->containsFileUploads()) {
            $requestBody = $request->getMultipartBody();
            $request->setHeaders(['Content-Type' => 'multipart/form-data; boundary=' . $requestBody->getBoundary()]);
        } else {
            $requestBody = $request->getUrlEncodedBody();
            $request->setHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
            ]);

        }

        if($request->getAccessToken() != null && $request->getAccessToken() != ''){
            $request->addHeader('Authorization', 'Bearer '. $request->getAccessToken());
        }

        return [
            $request->getEndpoint(),
            $request->getMethod(),
            $request->getHeaders(),
            $requestBody->getBody(),
        ];
    }

    /**
     * Makes the request to the Rest API and returns the results
     * @param AmikarRequest $request
     *
     * @return AmikarResponse
     *
     * @throws Exception\AmikarSDKException
     */
    public function sendRequest(AmikarRequest $request)
    {
//        if (get_class($request) === 'Amikar\AmikarRequest') {
//            $request->validateAccessToken();
//        }

        list($url, $method, $headers, $body) = $this->prepareRequestMessage($request);
        var_dump($url, $method, $headers, $body);
        // Since file uploads can take a while, we need to give more time for uploads
        $timeOut = static::DEFAULT_REQUEST_TIMEOUT;
        if ($request->containsFileUploads()) {
            $timeOut = static::DEFAULT_FILE_UPLOAD_REQUEST_TIMEOUT;
        }

        // Should throw `AmikarSDKException` exception on HTTP client error.
        // Don't catch to allow it to bubble up.
        $rawResponse = $this->httpClientHandler->send($url, $method, $body, $headers, $timeOut);

        var_dump($rawResponse);
        static::$requestCount++;

        $returnResponse = new AmikarResponse(
            $request,
            $rawResponse->getBody(),
            $rawResponse->getHttpResponseCode(),
            $rawResponse->getHeaders()
        );

        if ($returnResponse->isError()) {
            throw $returnResponse->getThrownException();
        }
        return $returnResponse;
    }

    /**
     * @return int
     */
    public static function getRequestCount()
    {
        return self::$requestCount;
    }

    /**
     * @param int $requestCount
     */
    public static function setRequestCount($requestCount)
    {
        self::$requestCount = $requestCount;
    }

    /**
     * @return AmikarGuzzleHttpHTTPClient
     */
    public function getHttpClientHandler()
    {
        return $this->httpClientHandler;
    }

    /**
     * @param AmikarGuzzleHttpHTTPClient $httpClientHandler
     */
    public function setHttpClientHandler($httpClientHandler)
    {
        $this->httpClientHandler = $httpClientHandler;
    }
}