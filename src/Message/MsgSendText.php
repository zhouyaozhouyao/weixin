<?php
namespace MaPing\Wechat\Message;
/**
 * 用来发送的文本消息
 * Class MsgSendText
 */
class MsgSendText extends MsgSend{
    public $content;
    public function __construct($touser,$content)
    {
        $this->touser=$touser;
        $this->msgtype='text';
        $this->content=$content;
    }
    public function toJson(){
        return parent::bound(array('content'=>$this->content));
    }
}
