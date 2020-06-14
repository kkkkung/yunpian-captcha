<?php

namespace Uptutu\YunpianCaptcha\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery\Mock;
use Uptutu\YunpianCaptcha\YunpianCaptcha;
use PHPUnit\Framework\TestCase;

class YunpianCaptchaTest extends TestCase
{

    public function testGetRequest()
    {
        $y = new YunpianCaptcha('mock-id', 'mock-key', 'mock-captchaId');

        $this->assertInstanceOf(Client::class, $y->getHttpClient());
    }

    public function testGetSecretId()
    {
        $y = new YunpianCaptcha('mock-id', 'mock-key', 'mock-captchaId');

        $this->assertEquals('mock-id', $y->getSecretId());
    }

    public function testGetSecretKey()
    {
        $y = new YunpianCaptcha('mock-id', 'mock-key', 'mock-captchaId');

        $this->assertEquals('mock-key', $y->getSecretKey());
    }

    public function testGetCaptchaId()
    {
        $y = new YunpianCaptcha('mock-id', 'mock-key', 'mock-captchaId');

        $this->assertEquals('mock-captchaId', $y->getCaptchaId());
    }

    public function testSetParams()
    {
        $y = new YunpianCaptcha('mock-id', 'mock-key', 'mock-captchaId');

        $this->assertIsArray($y->getParams());
        $this->assertArrayNotHasKey('captchaId', $y->getParams());

        $y->setParam('captchaId', 'xxxxx');
        $this->assertArrayHasKey('captchaId', $y->getParams());

    }

    public function testGetParams()
    {
        $y = new YunpianCaptcha('mock-id', 'mock-key', 'mock-captchaId');

        $y->setParam('token', 'xxx');
        $this->assertIsArray($y->getParams());
        $this->assertArrayHasKey('token', $y->getParams());
    }

    public function testSetSignature()
    {
        $y = new YunpianCaptcha('mock-id', 'mock-key', 'mock-captchaId');

        $data = [
            'token'        => 'mock-token',
            'authenticate' => 'mock-authenticate',
            'version'      => '1.0',
            'timestamp'    => time(),
            'nonce'        => random_int(1, 99999)
        ];

        $y->setParams($data);

        $this->assertArrayHasKey('signature', $y->getParams());

        if (!in_array('secretId', $data))
            $data['secretId'] = $y->getSecretId();
        if (!in_array('captchaId', $data))
            $data['captchaId'] = $y->getCaptchaId();

        $str = '';
        ksort($data);
        foreach ($data as $k => $v) {
            if ($v) {
                $str .= $k . $v;
            }
        }
        $str .= $y->getSecretKey();
        $signStr = md5($str);
        $this->assertEquals($signStr, $y->getParams()['signature']);

    }

    public function TestGetResultWithMsg()
    {
        $response = new Response(200, [], '{"code":1,"msg":"xxxx"}');

        $apiRequest = \Mockery::mock(Client::class);
        $apiRequest->allows()->post('https://captcha.yunpian.com/v1/api/authenticate',
            [
                'token'        => 'mock-tiken',
                'authenticate' => 'mock-authenticate',
                'version'      => '1.0',
                'timestamp'    => time(),
                'nonce'        => random_int(1, 99999)
            ], 'form_params')->andReturn($response);

        $y = new YunpianCaptcha('mock-id', 'mock-key', 'mock-captchaId');
        $response = $y->getCheckedResponseContent();

        $this->assertSame(['code' => 1, 'msg' => 'xxx'], $response);
    }

    public function test_it_can_check_with_request_array()
    {
        $y = new YunpianCaptcha('mock-id', 'mock-key', 'mock-captchaId');
        $request = ['token' => 'mock-token', 'authenticate' => 'mock-authenticate'];

        $response = new Response(200, [], '{"code":0,"msg":"xxxx"}');

        $apiRequest = \Mockery::mock(Client::class);
        $apiRequest->shouldReceive('post')->withAnyArgs()
            ->andReturn($response);

        $result = $y->useHttpClient($apiRequest)->checkRequest($request);

        $this->assertIsBool(true, $result);
    }

}