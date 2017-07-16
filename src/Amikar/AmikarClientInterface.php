<?php
/**
 * Created by PhpStorm.
 * User: elmehdi
 * Date: 16/07/17
 * Time: 16:49
 */

namespace Amikar;

/**
 * Interface AmikarClientInterface
 * @package Amikar
 */
interface AmikarClientInterface
{
    /**
     * Prepares the request for sending to the client handler.
     *
     * @param AmikarRequest $request
     *
     * @return array
     */
    public function prepareRequestMessage(AmikarRequest $request);

    /**
     * Makes the request to the Rest API and returns the results
     * @param AmikarRequest $request
     *
     * @return AmikarResponse
     */
    public function sendRequest(AmikarRequest $request);


}