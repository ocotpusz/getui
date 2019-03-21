<?php
return [
    // APP_EVN
    'app_env' => env('APP_ENV', 'development'),

    // The default default_client name which configured in `development` or `production` section
    'default_client' => 'client_1',

    'development' => [
        'client_1' => [
            'appid' => 'vo0w6rF2GI9wp05Y2kDfw7',
            'appkey' => '5VkTrvxC297bR72CpahE17',
            'appsecret' => 'Q0qaKnJgAv8mV5jBMzbwZ2',
            'mastersecret' => 'Iojg2xJPaN99ufWmlev9x2',
            'domainurl' => 'http://sdk.open.api.igexin.com/apiex.htm',
        ],
        // other client
    ],
];
