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

namespace Amikar\Tests\HttpClient;

use Amikar\Amikar;
use Amikar\Http\AmikarRawResponse;
use Amikar\HttpClient\AmikarGuzzleHttpHTTPClient;

class AmikarGuzzleHttpHTTPClientTest  extends \PhpUnit_Framework_TestCase
{
    private $client;

    protected function setUp()
    {
        $this->client = new AmikarGuzzleHttpHTTPClient(
            [
                'client_id' => 'amikar-sdk-client',
                'client_secret' => 'secret',
                'base_uri' => 'http://localhost:8081/api/v1.0/'
            ]
        );

    }

    public function testHttpClientHandlerCanBeInjected()
    {
        $this->assertInstanceOf(AmikarGuzzleHttpHTTPClient::class, $this->client);
    }

    public function testHttpClientCanSentAmikarRequestToApi()
    {
        $body = http_build_query(['grant_type' => 'client_credentials', 'scope' => 'two_legged_scope'], null, "&");
        $headers = ['Content-Type' => 'application/x-www-form-urlencoded'];
        /** @var AmikarRawResponse $rawResp */
        $rawResp = $this->client->send('/oauth/token','POST', $body, $headers, 0);
        var_dump($rawResp);
        $this->assertInstanceOf(AmikarRawResponse::class, $rawResp);
        $token = $rawResp->getBodyContentsAsArray();
        var_dump($token);
        $this->assertArrayHasKey('access_token', $token);
        $this->assertArrayHasKey('expires_in', $token);
        $this->assertArrayHasKey('scope', $token);
        $this->assertTrue($token['scope'] == "two_legged_scope");

    }
}
