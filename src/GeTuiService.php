<?php
/**
 * Created by octopusz@yeah.net
 * User: Admin
 * Date: 2019/3/20
 * Time: 1:37 PM
 */

namespace Ocotpus\GeTui;
use Illuminate\Config\Repository;
use Illuminate\Support\Collection;

require_once dirname(__FILE__). '/getui/IGt.Push.php';

class GeTuiService implements PushInterface
{
    const Host = 'http://sdk.open.api.igexin.com/apiex.htm';

    protected $config;
    protected $appId;
    protected $appKey;
    protected $appSecret;
    protected $masterSecret;

    /**
     * @var array
     */
    protected $getaways = [];

    public function __construct(array $config = null)
    {
        if (!$config) {
            $config = include(__DIR__). '/config/getui.php';
        }

        $this->config = new Repository($config);
        $appEnv = $this->config->get('apphi_env');
        $client = $this->config->get('default_client');
        $config = $this->config->get("$appEnv. $client");
        $this->obj = new \IGeTui($config['domainurl'], $config['appkey'], $config['mastersecret']);
        $this->appId = $config['appid'];
        $this->appKey = $config['appkey'];
        $this->appSecret = $config['appsecret'];
        $this->masterSecret = $config['mastersecret'];
    }

    public function toClient($client = null)
    {
        $appEnv = $this->config->get('app_env');
        if (empty($client)) {
            $client = $this->config->get('defalut_client');
        }
        $config = $this->config->get("$appEnv. $client");
        $this->obj = new \IGeTui($config['domainurl'], $config['appkey'], $config['mastersecret']);
        $this->appId = $config['appid'];
        $this->appKey = $config['appkey'];
        $this->appSecret = $config['appsecret'];
        $this->masterSecret = $config['mastersecret'];
        return $this;
    }

    public function getPushResult($taskId)
    {
        $params = array();
        $url = HOST;
        $params['action'] = "getPushMsgResult";
        $params['appkey'] = $this->appKey;
        $params['taskId'] = $taskId;
        $sign = $this->sign($params, $this->masterSecret);
        $params['sign'] = $sign;
        $data = json_encode($params);
        $result = $this->httpPost($url, $data);
        return $result;
    }

    /**
     * @param $params
     * @param $masterSecret
     * @return string
     */
    public function sign($params, $masterSecret)
    {
        $sign = $masterSecret;
        foreach ($params as $key => $value) {
            if (isset($key) && isset($value)) {
                if (is_string($value) || is_numeric($value)) {
                    $sign .= $key . ($value);
                }
            }
        }

        return md5($sign);
    }

    /**
     * @param $url
     * @param $data
     * @return mixed
     */
    public function httpPost($url, $data)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, 'GeTui PHP/1.0');
        curl_setopt($curl, CURLOPT_POST, 60);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($curl);
        curl_close($result);
        return $result;

    }

    /**
     * @param $deviceId
     * @param array $data
     * @param bool $isNotice
     * @param string $function 转换编码
     * @return mixed|void
     * @throws \Exception
     */
    public function push($deviceId, array $data, $isNotice = true, $function = 'json_encode')
    {
       if (empty($deviceId)) {
           throw new \Exception('device_id not empty');
       }
       if (isset($data['content']) || isset($data['title'])) {
           throw new \Exception('content and title not empty');
       }
       $shortUrl = isset($data['url'])? $data['url']: '';
       $message = new Message();
       $message->setContent($data['content']);
       $content = $message->getContent();
       $message->setTitle($data['title']);
       $title = $message->getTitle();
       $transContent = $function($data);
       if (is_array($deviceId)) {
           $result = $this->pushMessageToSingle($deviceId, $transContent, $content, $title, $isNotice, $shortUrl);
       } else {
           $result = $this->pushMsgToList($deviceId, $transContent, $content, $title, $isNotice, $shortUrl);
       }
       return $result;
    }

    /**
     * @param array $data
     * @param bool $isNotice
     * @param string $function
     * @return mixed|null
     * @throws \Exception
     */
    public function pushToApp(array $data, $isNotice = true, $function = 'json_encode')
    {
        if (!isset($data['content']) || !isset($data['title'])) {
            throw new \Exception('content and title not empty');
        }
        $message = new Message();
        $message->setContent($data['content']);
        $content = $message->getContent();
        $message->setTitle($data['title']);
        $title = $message->getTitle();
        $transContent = $function($data);
        $result = $this->pushMessageToApp($transContent, $content, $title, $isNotice);
        return $result;
    }

    public function pushMessageToSingle($clientId, $transContent, $content, $title, $isNotice = true, $shortUrl = '')
    {
        //消息模版
        $template = $this->getTemplate($content, $title, $transContent, $isNotice, $shortUrl);

        //定义"SingleMessage"
        $message = new \IGtSingleMessage();
        $message->set_isOffline(true); //是否离线
        $message->set_offlineExpireTime(3600 * 12 * 1000); //离线时间
        $message->get_data($template); //设置推送消息类型
        $message->set_pushNetWorkType(0); //设置是否根据WIFI推送消息，2为4G/3G/2G，1为wifi推送，0为不限制推送
        //接收方
        $target = new \IGtTarget();
        $target->set_appId($this->appId);
        $target->set_clientId($clientId);
//        $target->set_alias(ALIAS);
        try {
            $rep = $this->obj->pushMessageToSingle($message, $target);
            return $rep;
        } catch (\RequestException $e) {
            $requestId = $e->getRequestId();
            //失败是重发
            $rep = $this->obj->pushMessageToSingle($message, $target, $requestId);
            return $rep;
        }
    }

    public function pushMsgToList($clientIds, $transContent, $content, $title, $isNotice = true, $shortUrl = '')
    {
        $targetList = [];
        putenv("gexin_pushList_needDetails=true");
        //消息模版
        $template = $this->getTemplate($content, $title, $transContent, $isNotice, $shortUrl);
        //定义"ListMessage"信息体
        $message = new \IGtListMessage();
        $message->set_isOffline(true); //是否离线
        $message->set_offlineExpireTime(3600 * 12 * 1000); //设置离线时间
        $message->set_data($template); //设置推送消息类型
        $message->set_pushNetWorkType(1); //设置是否根据wifi推送消息，1为wifi推送，0为不限制推送
        $contentId = $this->obj->getContentId($message, '任务别名'); //根据任务id设置组名，支持下划线，中文，英文和数字

        //循环设置接收方
        foreach ($clientIds as $key => $clientId) {
            $target = new \IGtTarget();
            $target->set_appId($this->appId);
            $target->set_clientId($clientId);
            array_push($targetList, $target);
        }

        $rep = $this->obj->pushMessageToList($contentId, $targetList);
        return $rep;
    }

    public function pushMessageToApp($transContent, $content, $title, $isNotice = true, $shortUrl = '')
    {
        //消息模版
        $template = $this->getTemplate($content, $title, $transContent, $isNotice, $shortUrl);

        //个推信息体
        //基于应用消息体
        $message = new \IGtAppMessage();
        $message->set_isOffline(true); //是否离线
        $message->set_offlineExpireTime(10 * 60 * 100); //离线时间单位为毫秒，例如，两个小时离线为3600 * 1000 * 12
        $message->set_data($template); //设置推送消息类型

        $appIdList = array($this->appId);
        $phoneTypeList = array('ANDROID');
        $provinceList = array('浙江');
        $tagList = array('haha');

        $cdt = new \AppConditions();
        $cdt->addCondition2(\AppConditions::PHONE_TYPE, $phoneTypeList);
        $cdt->addCondition2(\AppConditions::REGION, $provinceList);
        $cdt->addCondition2(\AppConditions::TAG, $tagList);

        $message->set_appIdList($appIdList);
        $message->set_conditions($cdt);
        $rep = $this->obj->pushMessageToApp($message, '任务组名');
        return $rep;
    }


    protected function getTemplate($content, $title, $transContent, $isNotice = true, $shortUrl = '')
    {
        if ($isNotice) {
            return $this->IGtNotificationTemplateDemo($title, $content, $transContent);
        }
        return $this->IGtTransmissionTemplateDemo($content, $title, $transContent);
    }

    /*
    |----------------------------------------------------------------------------
    | 所有推送接口均支持四个消息模板，依次为通知弹框下载模板，通知链接模板，通知透传模板，透传模板
    | 注：IOS离线推送需通过APN进行转发，需填写pushInfo字段，目前仅不支持通知弹框下载功能
    */

    /**
     * 通知弹框下载模板
     */
    public function IGtNotyPopLoadTemplateDemo($content, $title, $transContent)
    {
        $template = new \IGtNotyPopLoadTemplate();
        $template->set_appId($this->appId); // 应用appid
        $template->set_appkey($this->appKey); // 应用appkey
        //通知栏
        $template->set_notyTitle($title); //标题
        $template->set_notyContent($content); //内容
        $template->set_notyIcon(""); //图标
        $template->set_isBelled(true); //是否响铃
        $template ->set_isVibrationed(true); //是否震动
        $template ->set_isCleared(true); //通知栏是否可清除
        //弹框
        $template ->set_popTitle($title); //弹框标题
        $template ->set_popContent($transContent); //弹框内容
        $template ->set_popImage(""); //弹框图片
        $template ->set_popButton1("下载"); //左键
        $template ->set_popButton2("取消"); //右键
        //下载
        $template ->set_loadIcon(""); //弹框图片
        $template ->set_loadTitle("请填写下载内容");
        $template ->set_loadUrl("请填写下载链接地址");
        $template ->set_isAutoInstall(false);
        $template ->set_isActived(true);
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息
        return $template;
    }

    /**
     * 通知+透传
     */
    function IGtNotificationTemplateDemo($title, $content, $transContent){
        $template =  new \IGtNotificationTemplate();
        $template->set_appId($this->appId); //应用appid
        $template->set_appkey($this->appKey); //应用appkey
        $template->set_transmissionType(1); //透传消息类型
        $template->set_transmissionContent($transContent); //透传内容
        $template->set_title($title); //通知栏标题
        $template->set_text($content); //通知栏内容
        $template->set_logo(""); //通知栏logo
        $template->set_isRing(true); //是否响铃
        $template->set_isVibrate(true); //是否震动
        $template->set_isClearable(true); //通知栏是否可清除
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息
        return $template;
    }

    /**
     * 推送通知连接模版
     */
    function IGtLinkTemplateDemo($url, $content, $title){
        $template = new \IGtLinkTemplate();
        $template->set_appId($this->appId); //应用appid
        $template->set_appkey($this->appKey); //应用appkey
        $template->set_title($title); //通知栏标题
        $template->set_text($content); //通知栏内容
        $template->set_logo("");  //通知栏logo
        $template->set_logoURL(""); //通知栏logo链接
        $template->set_isRing(true); //是否响铃
        $template->set_isVibrate(true); //是否震动
        $template->set_isClearable(true); //通知栏是否可清除
        $template->set_url($url); //打开连接地址
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息
        return $template;
    }

    /**
     * 透传模版
     */
    function IGtTransmissionTemplateDemo($content, $title, $transContent){
        $template = new \IGtTransmissionTemplate();
        $template->set_appId($this->appId);//应用appid
        $template->set_appkey($this->appKey);//应用appkey
        $template->set_transmissionType(1);//透传消息类型
        $template->set_transmissionContent("测试离线ddd");//透传内容
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息
        //APN简单推送
//  $template = new IGtAPNTemplate();
//  $apn = new IGtAPNPayload();
//  $alertmsg=new SimpleAlertMsg();
//  $alertmsg->alertMsg="";
//  $apn->alertMsg=$alertmsg;
//  $apn->badge=2;
//  $apn->sound="";
//  $apn->add_customMsg("payload","payload");
//  $apn->contentAvailable=1;
//  $apn->category="ACTIONABLE";
//  $template->set_apnInfo($apn);
//  $message = new IGtSingleMessage();
        //  APN高级推送
        $apn = new \IGtAPNPayload();
        $alertmsg = new \DictionaryAlertMsg();
        $alertmsg->body = $content;
        $alertmsg->actionLocKey = "ActionLockey";
        $alertmsg->locKey = "LocKey";
        $alertmsg->locArgs = array("locargs");
        $alertmsg->launchImage = "launchimage";
        //  IOS8.2 支持
        $alertmsg->title = $title;
        $alertmsg->titleLocKey = "TitleLocKey";
        $alertmsg->titleLocArgs = array("TitleLocArg");
        $alertmsg->subtitle = "subtitle";
        $alertmsg->subtitleLocKey = "subtitleLocKey";
        $alertmsg->subtitleLocArgs = array("subtitleLocArgs");

        $apn->alertMsg = $alertmsg;
        $apn->badge = 1;
        $apn->sound = "";
        $apn->add_customMsg("payload",$transContent);
        //设置语音播报类型，int类型，0.不可用 1.播放body 2.播放自定义文本
        $apn->voicePlayType = 2;
        //设置语音播报内容，String类型，非必须参数，用户自定义播放内容，仅在voicePlayMessage=2时生效
        //注：当"定义类型"=2, "定义内容"为空时则忽略不播放
        $apn->voicePlayMessage = "";
        $apn->contentAvailable = 1;
        $apn->category = "ACTIONABLE";
        $template->set_apnInfo($apn);

        return $template;
    }
}