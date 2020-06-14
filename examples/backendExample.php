<?php

namespace Example;

use Uptutu\YunpianCaptcha\YunpianCaptcha;

$secretId = "xxxxxxxxxxx";
$secretKey = "xxxxxxxxxx";
$captchaId = "xxxxxxxxxx";

$y = new YunpianCaptcha($secretId, $secretKey, $captchaId);

$parmas = [
    'token' => 'token',
    'authenticate' => 'authenticate',
    'version' => '1.0',
    'timestamp' => time(),
    'nonce' => random_int(1, 99999)
];

$request = ['token' => '收到前端异步成功回调里的token', 'authenticate' => '收到前端异步成功回调里的authenticate'];
$y->checkRequest($request); // return False or True

// 建议如此使用
if ($y->checkRequest($request)){
    // Do Something
} else {
    // Do Something else
}


$y->setParams($parmas);
$y->check(); // return False or True

$result = $y->getCheckResponse();

var_dump($result);

if ($y->check()){
    // Do Something
} else {
    // Do Something else
}
