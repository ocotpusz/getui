<?php
/**
 * Created by octopusz@yeah.net
 * User: Admin
 * Date: 2019/3/20
 * Time: 1:37 PM
 */

namespace Ocotpus\GeTui;


interface PushInterface
{
    /**
     * @param $deviceId
     * @param array $data
     * @return mixed
     */
    public function push($deviceId, array $data);

    /**
     * @param array $data
     * @return mixed
     */
    public function pushToApp(array $data);
}