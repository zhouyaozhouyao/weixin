<?php
namespace MaPing\Wechat\Message;;
/**
 * 接收到的链接 消息
 * Class MsgReceiveLink
 */
class MsgReceiveLink extends  MsgReceive
{
    public $Title, $Description, $Url;
    protected function __construct( $req)
    {
        $this->Title=$req->Title;
        $this->Description=$req->Description;
        $this->Url=$req->Url;
    }
}
