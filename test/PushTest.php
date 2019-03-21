<?php
/**
 * Created by zy@pupupula.com
 * User: Zhangyu
 * Date: 2019/3/20
 * Time: 11:57 AM
 */

namespace Octopus\GeTui\Test;
require_once dirname(__FILE__). '/../src/getui/IGt.Push.php';

use Ocotpus\GeTui\GeTuiService;
use PHPUnit\Framework\TestCase;

class PushTest extends TestCase
{
    protected $instance;

    public function setUp()
    {
        $file = dirname(__DIR__). '/src/config/getui.php';
        $config = include($file);
        $this->instance = GeTuiService($config);
    }

    public function testPushManager()
    {
        $this->assertInstanceOf(GeTuiService::class, $this->instance);
    }

    public function testPush()
    {
        echo PHP_EOL. "测试发送push中". PHP_EOL;
        try {
            $deviceId = "your devlce_id";
            $title = '我就试试，看看效果咋样';
            $content = '兰花';

            $data = [
                'title' => $title,
                'content' => $content
            ];
            $getuiResponse = $this->instance->push($deviceId, $data);
            echo json_encode($getuiResponse).PHP_EOL;
        } catch (\Exception $e) {
            $err = "Error : 错误：" . $e->getMessage();
            echo $err.PHP_EOL;
        }
    }
}