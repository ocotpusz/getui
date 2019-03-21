<?php
/**
 * Created by octopusz@yeah.net
 * User: Admin
 * Date: 2019/3/20
 * Time: 12:54 PM
 */

namespace Ocotpus\GeTui;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;

/**
 * Class GeTuiServiceProvider
 * @package Ocotpus\GeTui
 */
class GeTuiServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot()
    {
        $source = realpath(__DIR__. '/config/getui.php');

        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('getui.php')]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('getui');
        }
        $this->mergeConfigForm($source, 'getui');
    }

    public function register()
    {
//        $this->app->singleton(G)
    }
}

