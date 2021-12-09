<?php
namespace MaPing\Wechat\Message;
/**
 * 接收到的文本消息
 * Class MsgReceiveText
 */
class MsgReceiveText extends MsgReceive{
    public $Content;
    protected function __construct( $req)
    {
        $this->Content=$req->Content;
    }
}
