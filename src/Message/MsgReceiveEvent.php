<?php
namespace MaPing\Wechat\Message;;
/**
 * 接收到的事件消息
 * Class MsgReceiveEvent
 */
class MsgReceiveEvent extends MsgReceive{
    /**
     * 根据 消息类型,创建一个具体的事件消息实例
     * @param $req
     * @return MsgReceiveEventClick|MsgReceiveEventLocation|MsgReceiveEventScan|MsgReceiveEventScanSubscribe|MsgReceiveEventSubscribe|MsgReceiveEventUnSubscribe|MsgReceiveEventView
     */
    static  function createEventInstance( $req){
        switch($req->Event){
            case 'subscribe': //关注事件
                if(isset($req->EventKey) and $req->EventKey){
                    $msg=new MsgReceiveEventScanSubscribe($req);
                }else{
                    $msg=new MsgReceiveEventSubscribe($req);
                }
                break;
            case 'unsubscribe': //取消关注事件
                $msg=new MsgReceiveEventUnSubscribe($req);
                break;
            case 'CLICK': //点击菜单
                $msg=new MsgReceiveEventClick($req);
                break;
            case 'SCAN': //扫码事件
                $msg=new MsgReceiveEventScan($req);
                break;
            case 'LOCATION': //位置事件
                $msg=new MsgReceiveEventLocation($req);
                break;
            case 'VIEW': //查看菜单事件
                $msg=new MsgReceiveEventView($req);
                break;
            default:
                $msg=new MsgReceiveEventUnknown($req);
        }

        $msg->Event=$req->Event;
        return $msg;
    }

    //事件类型
    public $Event;
}
