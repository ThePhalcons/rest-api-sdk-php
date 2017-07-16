<?php

namespace Amikar\HttpClient;

/**
 * Interface AmikarHTTPClientInterface
 * @package Amikar\HttpClient
 */
interface AmikarHTTPClientInterface
{
    /**
     * @param string $endpoint  the endpoint to send the request to.
     * @param string $method    the request method.
     * @param string $body      the body of the request
     * @param array $headers    the request headers
     * @param int $timeout      the timeout in seconds to the request
     *
     * @return \Amikar\Http\AmikarRawResponse
     */
    public function send($endpoint, $method, $body, array $headers, $timeout);
    
}