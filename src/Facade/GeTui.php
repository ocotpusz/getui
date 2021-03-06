<?php
/**
 * Created by octopusz@yeah.net
 * User: Admin
 * Date: 2019/3/20
 * Time: 12:07 PM
 */

namespace Ocotpus\GeTui\Facade;

use Illuminate\Support\Facades\Facade;
use Ocotpus\GeTui\GeTuiService;

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