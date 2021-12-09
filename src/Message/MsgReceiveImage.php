<?php
namespace MaPing\Wechat\Message;;
/**
 * 接收到的图片消息
 * Class MsgReceiveImage
 */
class MsgReceiveImage extends MsgReceive{
    public $PicUrl,$MediaId;
    protected function __construct($req)
    {
        $this->PicUrl=$req->PicUrl;
        $this->MediaId=$req->MediaId;
    }
}
