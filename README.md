
```
Example:

```php
    $deviceId = '111111111111111';
   // 多个push对象device_id 用数组传入
//   $deviceId = [
//            '1111111111111',
//            '2222222222222',
//           ];        

   $data = [
                'url' => 'your url',
                'type' => 'your type',
                'title' => '随便',
                'content' => '你开心就好',
                'id' => '你的id',
                'merchant_name' => '泽明科技',
                'big_cat' => '电视机',
                'full_address' => '北京市海淀区五道口清华大学',
            ];

 
$res = \GeTui::push($deviceId, $data,true); //Using default default_client

print_r($res);


```

## License

MIT
