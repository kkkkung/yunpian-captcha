<?php
/**
 * Created by PhpStorm.
 * User: Alex Kung
 * Date: 12/14/2018
 * Time: 5:41 PM
 */

namespace Example;

use Yattao\YunpianCaptcha\YunpianCaptcha;

$secretId = "xxxxxxxxxxx";
$secretKey = "xxxxxxxxxx";
$captchaId = "xxxxxxxxxx";

$y = new YunpianCaptcha($secretId, $secretKey, $captchaId);

$parmas = [
    'captchaId' => 'yunpian.captchaId',
    'token' => 'token',
    'authenticate' => 'authenticate',
    'secretId' => 'yunpian.secretId',
    'version' => '1.0',
    'timestamp' => time(),
    'nonce' => random_int(1, 99999)
];

$y->setParams($parmas);
$y->setSignature();

$result = $y->getResultWithMsg();
var_dump($result);

if ($y->getResult()){
    // Do Something
} else {
    // Do Something else
}

// 可以链式操作
$result = $y->setParams($parmas)->setSignature()->getResultWithMsg();
