<?php
namespace MaPing\Wechat\Message;;
/**
 * 用来主动向用户发送的消息基类
 * Class MsgSend
 */
abstract class MsgSend{
    public $msgtype;
    public $touser;
    /**
     * 为消息结果XML附加 外轮廓
     * @param array $info
     * @return string
     */
    protected function bound(array $info){
        return json_encode(array(
            'touser'=>$this->touser,
            'msgtype'=>$this->msgtype,
            $this->msgtype=>$info
        ),JSON_UNESCAPED_UNICODE);
    }

    /**
     * 每个子类必须实现 的生成JSON串的方法
     * @return string
     */
    abstract public function toJson();
}
