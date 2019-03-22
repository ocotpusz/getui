# Laravel Or Lumen GeTui  

基于 [个推官方SDK](http://docs.getui.com/getui/server/php/start/)  for Laravel.

## Installing

```shell
$ composer require octopusz/getui -v
```
### Laravel



```php
// config/app.php

    'providers' => [
        //...
        Ocotpus\GeTui\GeTuiServiceProvider::class,    //This is default in laravel 5.5
    ],
```

And publish the config file: 

```shell
$ php artisan vendor:publish --provider=Ocotpus\\GeTui\\GeTuiServiceProvider
```

if you want to use facade mode, you can register a facade name what you want to use, for example `GeTui`: 

```php
// config/app.php

    'aliases' => [
        'GeTui' => Ocotpus\GeTui\Facade\GeTui::class,   //This is default in laravel 5.5
    ],
```

### lumen

- 在 bootstrap/app.php 中 82 行左右：
```
$app->register(Ocotpus\GeTui\GeTuiServiceProvider::class);
```
将 `vendor/ocotpusz/getui/src/config/getui.php` 拷贝到项目根目录`/config`目录下，并将文件名改成`getui.php`。

### configuration 

```php
// config/getui.php
   // APP_EVN     你的项目当前环境：测试 or 生产
    'app_env' => env('APP_ENV', 'development'),

   
    // The default default_client name which configured in `development` or `production` section
    //默认推送的客户端
    'default_client' => 'client',


    'development' => [
        'client' => [
            'appid' => 'your appid', //个推后后台获取相应app的参数
            'appkey' => 'your appkey',
            'appsecret' => 'your appsecret',
            'mastersecret' => 'your mastersecret',
            'gt_domainurl' => 'http://sdk.open.api.igexin.com/apiex.htm',
        ],
        // other client   .....
    ],
    'production' => [
            'client' => [
                'appid' => 'your appid', //个推后后台获取相应app的参数
                'appkey' => 'your appkey',
                'appsecret' => 'your appsecret',
                'mastersecret' => 'your mastersecret',
                'gt_domainurl' => 'http://sdk.open.api.igexin.com/apiex.htm',
            ],
            // other client   .....
    ],
    
```


## Usage

Gateway instance:

```php
use GeTui;
//针对单个或者多个用户推送
GeTui::push($deviceId, $data,true) //Using default default_client   推送给默认的客户端
GeTui::toClient('CLIENT NAME')->push($deviceId, $data)  // CLIENT NAME is key name of `development` or `production`  configuration.  //自定义发送的客户端  

// 针对整个app所有人推送
GeTui::pushToApp($data,true) ////Using default default_client  
GeTui::toClient('CLIENT NAME')->pushToApp($data)  // GATEWAY NAME is key name of `development` or `production`  configuration.

```


Example:

```php
    $deviceId = '111111111111111';
   // 多个push对象device_id 用数组传入
//   $deviceId = [
//            '111111111111111',
//            '222222222222222',
//           ];        

   $data = [
                'url' => '推送的url',
                'title' => '这是个调皮的推送',
                'content' => '想啥有啥',
            ];
$res = \GeTui::push($deviceId, $data,true); //Using default default_client
print_r($res)
```

## License

MIT
