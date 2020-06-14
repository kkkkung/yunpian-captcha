<?php

namespace Uptutu\YunpianCaptcha;

use Uptutu\YunpianCaptcha\Exceptions\InvalidArgumentException;
use GuzzleHttp\Client as HttpClient;

class YunpianCaptcha
{
    // 云片行为验证的请求地址
    protected string $captchaUrl = 'https://captcha.yunpian.com/v1/api/authenticate';

    // 云片 Secret id
    protected string $secretId;

    // 云片 Secret Key
    protected string $secretKey;

    // 云片 CaptchaId
    protected string $captchaId;

    // 运行传递的参数
    protected array $allowed = [
        'captchaId', 'token', 'authenticate', 'version', 'user', 'timestamp', 'secretId', 'nonce', 'signature'
    ];

    protected array $params = [];

    protected HttpClient $httpClient;

    public function __construct(string $secretId, string $secretKey, string $captchaId)
    {
        $this->secretKey = $secretKey;
        $this->secretId = $secretId;
        $this->captchaId = $captchaId;
    }

    /**
     * 获取初始化之后的请求实例
     *
     * @return HttpClient
     */
    public function getHttpClient()
    {
        $this->httpClient = $this->httpClient ?? new HttpClient();

        return $this->httpClient;
    }

    public function useHttpClient(HttpClient $client)
    {
        $this->httpClient = $client;

        return $this;
    }


    /**
     * 设置参数
     * 按照文档请设置如下的一些参数
     *
     * https://www.yunpian.com/doc/zh_CN/captcha/captcha_service.html
     * captchaId, token, authenticate, version, timestamp, nonce
     * secretId 和 signature 会通过 setSignature() 方法自动设置
     *
     *
     * @param $data
     *
     * @return $this
     */
    public function useParams(array $data)
    {
        $this->params = array_intersect_key($data, array_flip($this->allowed));

        return $this;
    }

    /**
     * 获取已经设置的参数
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * 获取 Secret ID
     *
     * @return string
     */
    public function getSecretId()
    {
        return $this->secretId;
    }

    /**
     * 获取 Secret Key
     *
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * 获取 Captcha Id
     *
     * @return string
     */
    public function getCaptchaId()
    {
        return $this->captchaId;
    }

    /**
     * 设置签名
     * 这一步是请求之前必须做的一步
     *
     * @return $this
     */
    protected function setSignature()
    {
        $params = array_keys($this->params);

        if (!in_array('secretId', $params))
            $this->setParams('secretId', $this->getSecretId());

        if (!in_array('captchaId', $params))
            $this->setParams('captchaId', $this->getCaptchaId());

        $signature = '';
        $params = $this->getParams();

        ksort($params);

        foreach ($params as $k => $v) {
            if ($v) {
                $signature .= $k . $v;
            }
        }

        $signature .= $this->getSecretKey();
        $signature = md5($signature);

        $this->setParams('signature', $signature);

        return $this;
    }

    /**
     * 获取请求结果
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function check()
    {
        $data = array_filter($this->getParams());

        $this->validParamsCheck();

        $response = $this->getHttpClient()->post($this->captchaUrl, [
            'form_params' => $data
        ]);

        if (200 != $response->getStatusCode()) {
            throw new InvalidArgumentException($response->getBody()->getContents());
        }

        $response = json_decode($response->getBody()->getContents());

        if (0 != $response['code']) {
            return false;
        }

        return true;
    }

    /**
     * 获取验证结果并返回结果数组
     * ['code' => 0, 'msg' => 'xxxx']
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    public function getCheckResponse()
    {
        $this->validParamsCheck();

        $data = array_filter($this->getParams());
        $response = $this->getHttpClient()->post($this->captchaUrl, ['form_params' => $data]);

        return json_decode($response->getBody()->getContents(), true);

    }

    public function setParams(string $key, $val)
    {
        $this->params[$key] = $val;
    }

    protected function validParamsCheck()
    {
        if (count($this->getParams()) < count($this->allowed)) {
            throw new InvalidArgumentException('请为应用配置正确的参数');
        }
    }
}