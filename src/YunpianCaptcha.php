<?php
/**
 * Created by PhpStorm.
 * User: Alex Kung
 * Date: 12/12/2018
 * Time: 14:53
 */

namespace Yattao\YunpianCaptcha;

use Yattao\ApiRequest\ApiRequest;
use Yattao\YunpianCaptcha\Exceptions\HttpException;
use Yattao\YunpianCaptcha\Exceptions\InvalidArgumentException;

class YunpianCaptcha
{
    // 云片行为验证的请求地址
    protected $captchaUrl = 'https://captcha.yunpian.com/v1/api/authenticate';

    // 云片 Secret id
    protected $secretId;

    // 云片 Secret Key
    protected $secretKey;

    // 云片 CaptchaId
    protected $captchaId;

    // 运行传递的参数
    protected $allowArr = [
        'captchaId', 'token', 'authenticate',
        'version', 'user', 'timestamp',
        'secretId', 'nonce', 'signature'
    ];

    protected $paramsData = [];

    protected $apiRequest;

    public function __construct(string $secretId, string $secretKey, string $captchaId)
    {
        $this->secretKey = $secretKey;
        $this->secretId = $secretId;
        $this->captchaId = $captchaId;
        $this->apiRequest = new apiRequest();
    }

    /**
     * 获取初始化之后的请求实例
     *
     * @return ApiRequest
     */
    public function getRequest()
    {
        return $this->apiRequest;
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
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setParams($data)
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('数据[ '. $data . ' ]不是一个能处理的数组');
        }
        foreach ($data as $k => $v) {
            if (in_array($k, $this->allowArr)) {
                $this->paramsData[$k] = $v;
            } else {
                throw new InvalidArgumentException($k . ' 不是一个合法的传入参数');
            }
        }
        return $this;
    }

    /**
     * 获取已经设置的参数
     *
     * @return array
     */
    public function getParams()
    {
        return $this->paramsData;
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
     * @throws InvalidArgumentException
     */
    public function setSignature()
    {
        if (in_array('signature',$this->getParams()))
            unset($this->paramsData['signature']);
        if (!in_array('secretId',$this->getParams()))
            $this->setParams(['secretId' => $this->getSecretId()]);
        if (!in_array('captchaId',$this->getParams()))
            $this->setParams(['captchaId' => $this->getCaptchaId()]);
        $str = '';
        $data = $this->getParams();

        ksort($data);
        foreach ($data as $k => $v) {
            if ($v) {
                $str .= $k . $v;
            }
        }
        $str .= $this->getSecretKey();
        $signStr = md5($str);
        $this->setParams(['signature' => $signStr]);
        return $this;
    }

    /**
     * 获取请求结果
     *
     * @throws HttpException 请求错误
     *
     * @return bool
     */
    public function getResult()
    {
        $data = array_filter($this->getParams());
        $res = $this->getRequest()
            ->post($this->captchaUrl, $data, 'form_params');
        if (isset($res['err_msg'])) throw new HttpException("请求失败: " . $res['err_msg']);

        $res = json_decode($res['content'], true);

        if (0 === $res['code']) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * 获取验证结果并返回结果数组
     * ['code' => 0, 'msg' => 'xxxx']
     *
     * @throws HttpException
     *
     * @return array
     */
    public function getResultWithMsg()
    {
        $data = array_filter($this->getParams());
        $res = $this->getRequest()
            ->post($this->captchaUrl, $data, 'form_params');
        if (isset($res['err_msg'])) throw new HttpException("请求失败: " . $res['err_msg']);

        return json_decode($res['content'], true);

    }
}