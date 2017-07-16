<?php

namespace Amikar\HttpClient;


use Amikar\Exception\AmikarSDKException;
use Amikar\Http\AmikarRawResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use Psr\Http\Message\ResponseInterface;

class AmikarGuzzleHttpHTTPClient implements AmikarHTTPClientInterface
{

    /** @var  Client */
    protected $guzzleClient;

    /**
     * AmikarGuzzleHttpHTTPClient constructor.
     * @param array $config
     * @throws AmikarSDKException
     * @internal param $client_id
     * @internal param $client_secret
     * @internal param Client $guzzleClient
     */
    public function __construct(array $config = [])
    {
        if (!$config['client_id']) {
            throw new AmikarSDKException('Required "client_id" key not supplied in config."');
        }
        if (!$config['client_secret']) {
            throw new AmikarSDKException('Required "client_secret" key not supplied in config."');
        }
        if (!$config['base_url']) {
            throw new AmikarSDKException('Required "base_url" key not supplied in config."');
        }
        if (!$config['version']) {
            throw new AmikarSDKException('Required "version" key not supplied in config."');
        }

        $this->guzzleClient = new Client([
            'base_uri' => $config['base_url'],
            'version' => $config['version'],
            'defaults' => [
                'auth' => [$config['client_id'], $config['client_secret']],
                'headers' => ['source' => 'rest-api-sdk-php'],
            ]
        ]);
    }


    /**
     * @param string $endpoint the endpoint to send the request to.
     * @param string $method the request method.
     * @param string $body the body of the request
     * @param array $headers the request headers
     * @param int $timeout the timeout in seconds to the request
     * @return AmikarRawResponse
     * @throws AmikarSDKException
     */
    public function send($endpoint, $method, $body, array $headers, $timeout = 0)
    {
        $options = [
            'headers' => $headers,
            'body' => $body,
            'timeout' => $timeout,
            'connect_timeout' => 10
        ];
        $request = $this->guzzleClient->request($method, $endpoint, $options);

        try {
            $rawResponse = $this->guzzleClient->send($request);
        } catch (RequestException $e) {
            $rawResponse = $e->getResponse();
            if ($e->getPrevious() instanceof TooManyRedirectsException || !$rawResponse instanceof ResponseInterface) {
                throw new AmikarSDKException($e->getMessage(), $e->getCode());
            }
        }

        $rawHeaders = $this->getHeadersAsString($rawResponse);
        $rawBody = $rawResponse->getBody();
        $httpStatusCode = $rawResponse->getStatusCode();
        return new AmikarRawResponse($rawHeaders, $rawBody, $httpStatusCode);
    }

    /**
     * Returns the Guzzle array of headers as a string.
     *
     * @param ResponseInterface $response The Guzzle response.
     *
     * @return string
     */
    public function getHeadersAsString(ResponseInterface $response)
    {
        $headers = $response->getHeaders();
        $rawHeaders = [];
        foreach ($headers as $name => $values) {
            $rawHeaders[] = $name . ": " . implode(", ", $values);
        }
        return implode("\r\n", $rawHeaders);
    }

    /**
     * @return Client
     */
    public function getGuzzleClient()
    {
        return $this->guzzleClient;
    }

    /**
     * @param Client $guzzleClient
     */
    public function setGuzzleClient($guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }


}