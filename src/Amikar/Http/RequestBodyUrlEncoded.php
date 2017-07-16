<?php
/**
 * Created by PhpStorm.
 * User: elmehdi
 * Date: 16/07/17
 * Time: 18:03
 */

namespace Amikar\Http;


class RequestBodyUrlEncoded
{
    /**
     * @var array The parameters to send with this request.
     */
    protected $params = [];
    /**
     * Creates a new GraphUrlEncodedBody entity.
     *
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }
    /**
     * @inheritdoc
     */
    public function getBody()
    {
        return http_build_query($this->params, null, '&');
    }
}