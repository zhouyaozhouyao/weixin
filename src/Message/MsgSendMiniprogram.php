<?php
namespace MaPing\Wechat\Message;;
/**
 * 用来发送小程序卡片消息
 * Class MsgSendMiniprogram
 */
class MsgSendMiniprogram extends MsgSend{
    public $miniprogrampage;
    public function __construct($touser,array $miniprogrampage=array())
    {
        $this->touser=$touser;
        $this->msgtype='miniprogrampage';
        $this->miniprogrampage=$miniprogrampage;
    }

    public function add(MsgSendMiniprogrampageItem $item){
        $this->miniprogrampage[]=$item;
    }

    public function toJson(){
        $array=[];
        foreach($this->miniprogrampage as $miniprogrampage){
            /* @var $miniprogrampage MsgSendMiniprogrampageItem */
            $array[]=$miniprogrampage->toArray();
        }
        return parent::bound(array(
            'miniprogrampage'=>$array
        ));
    }

}
