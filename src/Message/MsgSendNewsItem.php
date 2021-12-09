<?php
namespace MaPing\Wechat\Message;
/**
 * 用来发送的图文消息中的消息项
 * Class MsgSendNewsItem
 */
class MsgSendNewsItem{
    public $title,$description,$url,$picurl;
    public function __construct($title,$description,$url,$picurl)
    {
        $this->title=$title;
        $this->description=$description;
        $this->url=$url;
        $this->picurl=$picurl;
    }
    public function toArray(){
        return array(
            'title'=>$this->title,
            'description'=>$this->description,
            'url'=>$this->url,
            'picurl'=>$this->picurl
        );
    }

    public function toArrays($news){
        return array(
            'title'=>$news->title,
            'description'=>$news->description,
            'url'=>$news->url,
            'picurl'=>$news->picurl
        );
    }
}
