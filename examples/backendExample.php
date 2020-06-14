<?php

namespace Example;

use Uptutu\YunpianCaptcha\YunpianCaptcha;

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

if ($y->check()){
    // Do Something
} else {
    // Do Something else
}

// 可以链式操作
$result = $y->setParams($parmas)->setSignature()->getResultWithMsg();
