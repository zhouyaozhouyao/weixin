<?php
namespace MaPing\Wechat\Message;
    /**
     * 用来回复的消息基类
     * Class MsgReply
     *//**
* 用户通过微信向公众号发送的消息类 基类
* Class MsgReceive
*/
abstract class MsgReceive{
//禁止实例化
private function __construct(SimpleXMLElement $req)
{
}

/**
* 从GET参数中构造一个消息实例,用于开发和测试
* @return MsgReceiveImage|MsgReceiveLink|MsgReceiveText
*/
final static public function test(){
$info=(object)$_GET;
$instance=static::createInstance($info);
$instance->formal=false;
return $instance;
}

/**
* 构造一个正式的请求消息实例
* @return MsgReceiveImage|MsgReceiveLink|MsgReceiveText
*/
final static public function formal(){
$xml= file_get_contents('php://input');
$wxData = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
$instance=static::createInstance($wxData->children());
$instance->formal=true;
return $instance;
}

//以下是消息的常规字段,可参考微信开发文档
public $ToUserName;
public $FromUserName;
public $CreateTime;
public $MsgType;
public $MsgId;

/**
* 创建一个具体的消息实例
* @param $req
* @return MsgReceiveImage|MsgReceiveLink|MsgReceiveText
*/
static private function createInstance( $req){
switch($req->MsgType){
case 'text':
$msg= new MsgReceiveText($req);
break;
case 'image':
$msg=new MsgReceiveImage($req);
break;

case 'link':
$msg=new MsgReceiveLink($req);
break;

case 'event':
$msg= MsgReceiveEvent::createEventInstance($req);
break;
default:
$msg=new MsgReceiveUnknown($req);
}

//给消息中的常规字段赋值
$msg->MsgType=$req->MsgType;
$msg->ToUserName=$req->ToUserName;
$msg->FromUserName=$req->FromUserName;
$msg->CreateTime=$req->CreateTime;
$msg->MsgId=$req->MsgId;

//返回消息子类的实例
return $msg;
}

//是否是正式请求
public $formal;
}
