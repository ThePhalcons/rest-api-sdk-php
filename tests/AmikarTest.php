<?php
/**
 *  *
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
namespace Amikar\Tests;

use Amikar\Amikar;
use Amikar\HttpClient\AmikarGuzzleHttpHTTPClient;

class AmikarTest extends \PHPUnit_Framework_TestCase
{

    protected $config = [
        'client_id' => 'amikar-3legged',
        'client_secret' => 'amikar-3legged-secret',
        'version' => 'v1'
    ];

    /** @expectedException \Amikar\Exception\AmikarSDKException */
    public function testInstantiatingWithoutAppIdThrows()
    {
        $config = [
            'client_secret' => 'foo_secret',
        ];
        new Amikar($config);
    }

    /** @expectedException \Amikar\Exception\AmikarSDKException */
    public function testInstantiatingWithoutClientSecretThrows()
    {
        $config = [
            'client_id' => 'foo_secret',
        ];
        new Amikar($config);
    }

    public function testGuzzleHttpClientHandlerCanBeForced()
    {
        $api = new Amikar($this->config);
        $this->assertInstanceOf(AmikarGuzzleHttpHTTPClient::class, $api->getClient()->getHttpClientHandler()
        );
    }

}
