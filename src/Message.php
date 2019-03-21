<?php
/**
 * Created by zy@pupupula.com
 * User: Zhangyu
 * Date: 2019/3/20
 * Time: 1:38 PM
 */

namespace Ocotpus\GeTui;

/**
 * Class Message
 * @package Ocotpus\GeTui
 */
class Message
{
    protected $content;
    protected $title;

    /**
     * @param $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

}