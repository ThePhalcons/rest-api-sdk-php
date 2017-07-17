<?php

namespace Amikar\HttpClient;


use Amikar\Exception\AmikarSDKException;
use Amikar\Http\AmikarRawResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use GuzzleHttp\Psr7\Request;
use function GuzzleHttp\Psr7\str;
use Psr\Http\Message\ResponseInterface;

class AmikarGuzzleHttpHTTPClient implements AmikarHTTPClientInterface
{

    /** @var  Client */
    protected $guzzleClient;
    /** @var  array $config contains client config */
    private $config;

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

        if (!$config['base_uri']) {
            throw new AmikarSDKException('Required "base_url" key not supplied in config."');
        }

        $this->guzzleClient = new Client([
            'base_uri' => $config['base_uri']
        ]);

        $this->config = $config;
    }


    /**
     * @param string $endpoint the endpoint to send the request to.
     * @param string $method the request method.
     * @param string|array $body the body of the request
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
            'connect_timeout' => 10,
        ];

        // if Authorization header is not specified, set basic authentication
        if(!array_key_exists('Authorization', $headers)){
            $options = array_merge( $options, ['auth' => [$this->config['client_id'], $this->config['client_secret'] ]]);
        }

        // convert the endpoint to a relative url @see{ https://tools.ietf.org/html/rfc3986#section-5.2}
        if(substr($endpoint, 0, 1) == "/"){
            $endpoint = "." . $endpoint;
        }

        try {
            $rawResponse = $this->guzzleClient->request($method, $endpoint, $options);
        } catch (RequestException $e) {
            echo str($e->getRequest());
            if ($e->hasResponse()) {
                echo str($e->getResponse());
            }
            throw new AmikarSDKException($e->getMessage() . "\n" . str($e->getResponse()), $e->getCode());
        }
//        } catch (RequestException $e) {
//            $rawResponse = $e->getResponse();
//            if ($e->getPrevious() instanceof TooManyRedirectsException || !$rawResponse instanceof ResponseInterface) {
//                throw new AmikarSDKException($e->getMessage(), $e->getCode());
//            }
//        }

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