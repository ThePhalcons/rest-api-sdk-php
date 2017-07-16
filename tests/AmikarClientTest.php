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

/**
 * Created by PhpStorm.
 * User: elmehdi
 * Date: 16/07/17
 * Time: 20:43
 */

namespace Amikar\Tests;

use Amikar\AmikarApp;
use Amikar\AmikarClient;
use Amikar\AmikarRequest;
use Amikar\AmikarResponse;
use Amikar\HttpClient\AmikarGuzzleHttpHTTPClient;

class AmikarClientTest extends \PhpUnit_Framework_TestCase
{
    /** @var  AmikarApp */
    public $app;

    /** @var  AmikarClient */
    public $client;

    protected function setUp()
    {
        $this->app = new AmikarApp('amikar_client_id', 'amikar_client_secret');
        $this->client = new AmikarClient([
            'client_id' => 'amikar_client_id',
            'client_secret' => 'amikar_client_secret',
            'version'=>'v1',
            'base_url' => 'http://localhost:8081/{version}'
        ]);
    }


    public function testHttpClientHandlerCanBeInjected()
    {
        $this->assertInstanceOf(AmikarGuzzleHttpHTTPClient::class, $this->client->getHttpClientHandler());
    }

    public function testAAmikarRequestEntityCanBeUsedToSendARequestToApi()
    {
        $request = new AmikarRequest($this->app, 'token', 'GET', '/foo');
        $response = $this->client->sendRequest($request);
        $this->assertInstanceOf(AmikarResponse::class , $response);
        $this->assertEquals(200, $response->getHttpStatusCode());
        $this->assertEquals('{"data":[{"id":"123","name":"Foo"},{"id":"1337","name":"Bar"}]}', $response->getBody());
    }
}
