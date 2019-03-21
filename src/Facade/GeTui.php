<?php
/**
 * Created by zy@pupupula.com
 * User: Zhangyu
 * Date: 2019/3/20
 * Time: 12:07 PM
 */

namespace Ocotpus\GeTui\Facade;

use Illuminate\Support\Facades\Facade;
use Octopus\GeTui\GeTuiService;

/**
 * Class GeTui
 * @package Ocotpus\GeTui\Facade
 */
class GeTui extends Facade
{
    public static function getFacadeAccessor()
    {
        return GeTuiService::class;
    }
}