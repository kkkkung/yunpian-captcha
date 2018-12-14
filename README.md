<h1 align="center"> yunpian-captcha </h1>

<p align="center"> Encapsulates the interface of the request Yunpian captcha.</p>

## 安装

```shell
$ composer require yattao/yunpian-captcha -vvv
```

## 配置

在使用本扩展之前，你需要去 [云片平台](https://www.yunpian.com/) 注册账号，然后创建应用，获取应用的 secret Key 和 secret Id



## 前端接入云片行为验证

### WEB

#### 引入初始化 JS

```html
<script src="https://www.yunpian.com/static/official/js/libs/riddler-sdk-0.2.1.js" />
```

#### 配置验证对象

```js
new Riddler(options)
```

`options`对象为配置对象，具有如下参数：

| 参数名       | 类型                                           | 是否必须 | 含义                                                         |
| ------------ | ---------------------------------------------- | -------- | ------------------------------------------------------------ |
| onSuccess    | function(token:string, close:function)         | T        | 验证成功处理器, token: 验证token，close：关闭SDK的UI         |
| appId        | string                                         | T        | 应用标识, captchaId                                          |
| version      | string                                         | T        | 接口版本号                                                   |
| container    | HTMLElement                                    | T        | 验证逻辑绑定的元素                                           |
| noButton     | boolean                                        | T        | 是否在`container`内渲染按钮，当`mode`不为`flat`时有效        |
| mode         | string                                         | F        | UI接入方式，flat-直接嵌入，float-浮动，dialog-对话框, 默认`dialog` |
| onError      | function                                       | F        | 验证异常处理器。即当云片验证服务出现异常时，可以在此回调上处理，比如，不使用验证，或者，使用图片验证服务等。 |
| onFail       | function(code:int, msg:string, retry:function) | F        | 用户验证失败处理器, code: 错误码，msg: 错误信息，retry: 重试验证逻辑。默认实现为重新验证一次。 |
| beforeStart  | function(next:string)                          | F        | 进入验证逻辑前的 勾子，next: 继续执行后续逻辑                |
| expired      | int                                            | F        | 请求超时时间，单位秒，默认`30`                               |
| jsonpField   | string                                         | F        | jsonp处理器名，默认为`ypjsonp`                               |
| rsaPublicKey | string                                         | F        | 加密公钥，如非异常情况则无需设置                             |
| hosts        | string                                         | F        | 验证服务器地址，如非异常情况则无需设置                       |
| winWidth     | number                                         | F        | 窗口宽度，不小于300，默认500                                 |

#### 本地化(可选)

如果需要使用本地化的文案，可创建一个资源文件`riddler.local.js`，并在`riddler-sdk.js`之前引入即可。

替换``riddler.local.js`内中文即可

```js
window.YP_RIDDLER_RESOURCE = {
  '1': '点此进行验证',
  '2': '请按顺序点击:',
  '3': '按住按钮拖动拼图到所示位置',
  '4': '验证失败，请重试'
}
```

#### Demo

```html
<html>
<head>
  <!--可选，本地化文案-->
  <script src="/static/riddler-sdk.local.js"></script>

  <!--依赖-->
  <script src="https://www.yunpian.com/static/official/js/libs/riddler-sdk-0.2.1.js"></script>

  <!--初始化-->
  <script>

    window.onload = function () {

      // 初始化
      new YpRiddler({
        expired: 10,
        mode: 'dialog',
        container: document.getElementById('cbox'),
        appId: 'your-captchaId',
        version: 'v1',
        onError: function (param) {
          if(param.code == 429) {
            alert('请求过于频繁，请稍后再试！')
            return
          }
          // 异常回调
          console.error('验证服务异常')
        },
        onSuccess: function (validInfo, close) {
          // 成功回调
          alert(`验证通过！token=${validInfo.token}, authenticate=${validInfo.authenticate}`)
          close()
        },
        onFail: function (code, msg, retry) {
          // 失败回调
          alert('出错啦：' + msg + ' code: ' + code)
          retry()
        },
        beforeStart: function (next) {
         console.log('验证马上开始')
         next()
        },
        onExit: function() {
          // 退出验证 （仅限dialog模式有效）
          console.log('退出验证')
        }
      })
    }
  </script>
</head>

<body>
  <div id="cbox"></div>
</body>
</html>
```

#### 重点：

Demo 中可以看见回调成功后会有两个重要的值，token 和 authenticate 

将这两个值传给后端，后端将这两个值和一一些必要一起再请求云片，通过云片响应得到判断此次验证结果。

## 使用

### 实例化 SDK

```php
use Yattao\YunpianCaptcha\YunpianCaptcha;

$secretId = "xxxxxxxxxxx";
$secretKey = "xxxxxxxxxx";
$captchaId = "xxxxxxxxxx";

$y = new YunpianCaptcha($secretId, $secretKey, $captchaId);
  
```

### 设置必要参数

```php
$parmas = [
    'captchaId' => 'yunpian.captchaId',
    'token' => 'token',
    'authenticate' => 'authenticate',
    'secretId' => 'yunpian.secretId',
    'version' => '1.0',
    'timestamp' => time(),
    'nonce' => random_int(1,99999)
];

$y->setParams($parmas);
```



#### 请求参数

| 参数         | 类型   | 必填 | 备注                                                         |
| ------------ | ------ | ---- | ------------------------------------------------------------ |
| captchaId    | string | Y    | 验证产品 id                                                  |
| token        | string | Y    | 前端返回的 token，token 作为一次验证的标志。                 |
| authenticate | string | Y    | 用户验证通过后，返回的参数                                   |
| secretId     | string | Y    | 验证产品密钥 id                                              |
| version      | string | Y    | 版本，固定值`1.0`                                            |
| user         | string | F    | 可选值，接入方用户标志，如担心信息泄露，可采用摘要方式给出。 |
| timestamp    | string | Y    | 当前时间戳的毫秒值，如`1541064141441`                        |
| nonce        | string | Y    | 随机正整数, 在 1-99999 之间，与 timestamp 配合可以防止消息重放 |
| signature    | string | Y    | 签名信息                                                     |

- SDK 中的另外一个方法能计算出 符合要求的 signature 值，但前提是必须有相必要的值都被设置了。 captchaId、 secretId 和 signature 能被 setSignature() 方法自动传入选项。所以，可以不必在 setParams() 的方法中传入这三个字段 



### 设置签名

先来看看官方给出的 signature 计算方法：

#### 签名计算方法

1. 对所有请求参数（不包括 signature 参数），按照参数名ASCII码表升序顺序排序。如：foo=1， bar=2， foo_bar=3， baz=4 排序后的顺序是 bar=2， baz=4， foo=1， foo_bar=3 。
2. 将排序好的参数名和参数值构造成字符串，格式为：key1+value1+key2+value2… 。根据上面的示例得到的构造结果为：bar2baz4foo1foo_bar3 。
3. 选择与 secretId 配对的 secretKey ，加到上一步构造好的参数字符串之后，如 secretKey=`e3da918313c14ea8b25db31f01263f80` ，则最后的参数字符串为 `bar2barz4foo1foo_bar3e3da918313c14ea8b25db31f01263f80`
4. 把3步骤拼装好的字符串采用 utf-8 编码，使用 MD5 算法对字符串进行摘要，计算得到 signature  参数值，将其加入到接口请求参数中即可。MD5  是128位长度的摘要算法，用16进制表示，一个十六进制的字符能表示4个位，所以签名后的字符串长度固定为32位十六进制字符。上述签名的结果为：`59db908f26fb997c30b32ddb911485c2`

当然，你可以自己计算然后通过 setParams() 方法将 signature 值传递进去，但是这样的话你还要这个 SDK 做什么

只需要对实例化的 SDK 执行下面的方法

```php
$y->setSignature();
```

该方法会自动计算 signature 值并添加到请求项中，这一步，也会自动将实例化 SDK 时候的三个有用的值('secretId','secretKey', 'captchaId') 加入请求项

### 获取结果

该 SDK 有两个方式获取结果

1. ```php
   $y->getResult();
   ```

1. ```php
   $y->getResultWithMsg();
   ```

第一种方式返回的是一个请求结果，布尔值，此次验证的成功与否，True 或者 False

第二种方式返回的是该次请求返回的数据，也就是文档中的code 和 msg

#### 相应参数

| 参数 | 类型   | 必填 | 备注                   |
| ---- | ------ | ---- | ---------------------- |
| code | int    | Y    | 成功为0，非0为异常信息 |
| msg  | string | Y    | 错误描述信息           |



## 示例

```php
<?php

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
```



## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/yattao/yunpianCaptcha/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/yattao/yunpianCaptcha/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._

## License

MIT