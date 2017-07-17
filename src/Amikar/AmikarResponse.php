<?php
/**
 * Created by PhpStorm.
 * User: elmehdi
 * Date: 16/07/17
 * Time: 17:00
 */

namespace Amikar;


use Amikar\Exception\AmikarSDKException;

class AmikarResponse
{

    /**
     * @var int The HTTP status code response from Graph.
     */
    protected $httpStatusCode;
    /**
     * @var array The headers returned from Graph.
     */
    protected $headers;
    /**
     * @var string The raw body of the response from Graph.
     */
    protected $body;
    /**
     * @var array The decoded body of the Graph response.
     */
    protected $decodedBody = [];
    /**
     * @var AmikarRequest The original request that returned this response.
     */
    protected $request;
    /**
     * @var AmikarSDKException The exception thrown by this request.
     */
    protected $thrownException;

    /**
     * Creates a new Response entity.
     *
     * @param AmikarRequest $request
     * @param string|null $body
     * @param int|null $httpStatusCode
     * @param array|null $headers
     */
    public function __construct(AmikarRequest $request, $body = null, $httpStatusCode = null, array $headers = [])
    {
        $this->request = $request;
        $this->body = $body;
        $this->httpStatusCode = $httpStatusCode;
        $this->headers = $headers;
        $this->decodeBody();
    }

    /**
     * Convert the raw response into an array if possible.
     *
     * Graph will return 2 types of responses:
     * - JSON(P)
     *    Most responses from Graph are JSON(P)
     * - application/x-www-form-urlencoded key/value pairs
     *    Happens on the `/oauth/access_token` endpoint when exchanging
     *    a short-lived access token for a long-lived access token
     * - And sometimes nothing :/ but that'd be a bug.
     */
    public function decodeBody()
    {
        $this->decodedBody = json_decode($this->body, true);
        if ($this->decodedBody === null) {
            $this->decodedBody = [];
            parse_str($this->body, $this->decodedBody);
        } elseif (is_numeric($this->decodedBody)) {
            $this->decodedBody = ['id' => $this->decodedBody];
        }
        if (!is_array($this->decodedBody)) {
            $this->decodedBody = [];
        }
        if ($this->isError()) {
            $this->makeException();
        }
    }

    /**
     * @return int
     */
    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }

    /**
     * @param int $httpStatusCode
     */
    public function setHttpStatusCode($httpStatusCode)
    {
        $this->httpStatusCode = $httpStatusCode;
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
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return array
     */
    public function getDecodedBody()
    {
        return $this->decodedBody;
    }

    /**
     * @param array $decodedBody
     */
    public function setDecodedBody($decodedBody)
    {
        $this->decodedBody = $decodedBody;
    }

    /**
     * @return AmikarRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param AmikarRequest $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return AmikarSDKException
     */
    public function getThrownException()
    {
        return $this->thrownException;
    }

    /**
     * @param AmikarSDKException $thrownException
     */
    public function setThrownException($thrownException)
    {
        $this->thrownException = $thrownException;
    }


    /**
     * Returns true if Graph returned an error message.
     *
     * @return boolean
     */
    public function isError()
    {
        return isset($this->decodedBody['error']);
    }
    /**
     * Throws the exception.
     *
     * @throws AmikarSDKException
     */
    public function throwException()
    {
        throw $this->thrownException;

    }
    /**
     * Instantiates an exception to be thrown later.
     */
    public function makeException()
    {
        $this->thrownException = AmikarResponseException::create($this);
    }

    /**
     * Return the body of the response as json.
     *
     * @return mixed
     */
    public function getBodyContentAsJson()
    {
        return json_decode($this->body);
    }
    /**
     * Return the body of the response as array.
     *
     * @return mixed
     */
    public function getBodyContentsAsArray(){
        return json_decode($this->body, true);
    }
}