<?php
namespace MaPing\Wechat\Message;;
/**
 * 用来发送的图文消息中的消息项
 * Class MsgSendNewsItem
 */
class MsgSendMiniprogrampageItem{
    public $title,$description,$url,$picurl;
    public function __construct($title,$appid,$pagepath,$thumb_media_id)
    {
        $this->title=$title;
        $this->appid=$appid;
        $this->pagepath=$pagepath;
        $this->thumb_media_id=$thumb_media_id;
    }

    public function toArray(){
        return array(
            'title'=>$this->title,
            'appid'=>$this->appid,
            'pagepath'=>$this->pagepath,
            'thumb_media_id'=>$this->thumb_media_id
        );
    }

}
