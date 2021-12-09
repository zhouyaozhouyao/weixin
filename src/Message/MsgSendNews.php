<?php
namespace MaPing\Wechat\Message;
/**
 * 用来发送的图文消息
 * Class MsgSendNews
 */
class MsgSendNews extends MsgSend{
    public $articles;
    public function __construct($touser,array $articles=array())
    {
        $this->touser=$touser;
        $this->msgtype='news';
        $this->articles=$articles;
    }
    public function add(MsgSendNewsItem $item){
        $this->articles[]=$item;
    }

    public function toJson(){
        $array=[];
        foreach($this->articles as $article){
            /* @var $article MsgSendNewsItem */
            $array[]=$article->toArray();
        }
        return parent::bound(array(
            'articles'=>$array
        ));
    }

    public function toArrays($news){
        return array(
            'title'=>$news->Title,
            'description'=>$news->Description,
            'url'=>$news->Url,
            'picurl'=>$news->PicUrl
        );
    }

    public function toJsons(){
        if(count($this->articles)>8){
            return;
        }
        $array=[];

        foreach($this->articles as $article){

            /* @var $article MsgSendNewsItem */
            $array[]=$this->toArrays($article);
        }
        return parent::bound(array(
            'articles'=>$array
        ));
    }
}
