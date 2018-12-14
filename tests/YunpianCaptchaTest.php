<?php

namespace Yattao\YunpianCaptcha\Tests;

use GuzzleHttp\Psr7\Response;
use Yattao\ApiRequest\ApiRequest;
use Yattao\YunpianCaptcha\Exceptions\InvalidArgumentException;
use Yattao\YunpianCaptcha\YunpianCaptcha;
use PHPUnit\Framework\TestCase;

class YunpianCaptchaTest extends TestCase
{

    public function testGetRequest()
    {
        $y = new YunpianCaptcha('mock-id', 'mock-key', 'mock-captchaId');

        $this->assertInstanceOf(ApiRequest::class, $y->getRequest());
    }

    public function testGetSecretId()
    {
        $y = new YunpianCaptcha('mock-id', 'mock-key', 'mock-captchaId');

        $this->assertEquals('mock-id', $y->getSecretId());
    }

    public function testGetSecretKey()
    {
        $y = new YunpianCaptcha('mock-id', 'mock-key', 'mock-captchaId');

        $this->assertEquals('mock-key',$y->getSecretKey());
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

        $y->setParams(['captchaId' => 'xxxxx']);
        $this->assertArrayHasKey('captchaId', $y->getParams());

    }

    public function testGetParams()
    {
        $y = new YunpianCaptcha('mock-id', 'mock-key', 'mock-captchaId');

        $this->assertIsArray($y->getParams());

        $y->setParams(['token' => 'xxx']);
        $this->assertArrayHasKey('token', $y->getParams());
    }

    public function testSetSignature()
    {
        $y = new YunpianCaptcha('mock-id', 'mock-key', 'mock-captchaId');

        $data = [
            'token' => 'mock-token',
            'secretId' => 'mock-secretId',
            'captchaId' => 'captchaId',
            'authenticate' => 'mock-authenticate',
            'version' => '1.0',
            'timestamp' => time(),
            'nonce' => random_int(1, 99999)
        ];

        $this->assertInstanceOf(YunpianCaptcha::class, $y->setParams($data)->setSignature());
        $this->assertArrayHasKey('signature', $y->getParams());

        if (!in_array('secretId',$data))
            $data['secretId'] = $y->getSecretId();
        if (!in_array('captchaId',$data))
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

        $apiRequest = \Mockery::mock(ApiRequest::class);
        $apiRequest->allows()->post('https://captcha.yunpian.com/v1/api/authenticate',
            [
                'token' => 'mock-tiken',
                'authenticate' => 'mock-authenticate',
                'version' => '1.0',
                'timestamp' => time(),
                'nonce' => random_int(1, 99999)
            ], 'form_params')->andReturn($response);

        $y = \Mockery::mock(YunpianCaptcha::class, ['mock-key', 'mock-id'])->makePartial();
        $y->allows()->getRequest()->andrReturn($apiRequest);

        $this->assertSame(['code' => 1, 'msg' => 'xxx'], $y->getResult());
    }

    public function TestGetResult()
    {
        $response = new Response(200, [], '{"code":1,"msg":"xxxx"}');

        $apiRequest = \Mockery::mock(ApiRequest::class);
        $apiRequest->allows()->post('https://captcha.yunpian.com/v1/api/authenticate',
            [
                'token' => 'mock-tiken',
                'authenticate' => 'mock-authenticate',
                'version' => '1.0',
                'timestamp' => time(),
                'nonce' => random_int(1, 99999)
            ], 'form_params')->andReturn($response);

        $y = \Mockery::mock(YunpianCaptcha::class, ['mock-key', 'mock-id'])->makePartial();
        $y->allows()->getRequest()->andrReturn($apiRequest);

        $this->assertIsBool($y->getResult());
    }

    public function testSetParamsWithInvalidData()
    {
        $y = new YunpianCaptcha('mock-id', 'mock-key', 'mock-captchaId');

        $this->expectException(InvalidArgumentException::class);

        $data = 'test';
        $y->setParams($data);
        $this->expectExceptionMessage('数据[ ' . $data . ' ]不是一个能处理的数组');

        $data = ['test' => 'test'];
        $y->setParams($data);
        $this->expectExceptionMessage('test 不是一个合法的传入参数');

        $this->fail('Failed to assert setParams throw exception with invalid argument');


    }

}