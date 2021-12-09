<?php
/**
 * Created by PhpStorm.
 * User: maping
 * Date: 2021/12/8
 * Time: 上午11:39
 */
namespace MaPing\WeChat;


//处理公众号业务逻辑
use App\Wechat\Message\MsgSend;
use App\Wechat\Message\MsgSendMiniprogram;
use App\Wechat\Message\MsgSendMiniprogrampageItem;
use App\Wechat\Message\MsgSendNews;
use App\Wechat\Message\MsgSendNewsItem;
use Illuminate\Support\Facades\Log;
use Psr\Log\InvalidArgumentException;


class Wechat  {

    protected $officialAccount = '';
    protected $appId = '';
    protected $appSecret = '';
    protected $allowTypes = ['image', 'voice', 'video', 'thumb'];
    public function __construct($officialAccount)
    {
        $this->officialAccount = $officialAccount;
        $this->appId = $officialAccount['appId'];
        $this->appSecret = $officialAccount['appSecret'];
    }

    /**
     * 获取一个Token,如果需要,更新Token
     * @return bool
     */
    public function getAccesToken()
    {
        return static::updateAccessToken();
    }

    public function testDemo()
    {
        return ['status' => true, 'msg' => 'ok'];
    }

    /**
     * 从微信 更新Token
     * @return bool
     */
    private function updateAccessToken()
    {
        $appId = $this->appId;
        $secret = $this->appSecret;
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appId . '&secret=' . $secret;
        //请求微信获取TOKEN的接口
        $decode = json_decode((new Tools($url))->get());
        //请求失败
        if (isset($decode->errcode)) {
            return $decode->errmsg;
        }
        //请求成功,解码
        $token = $decode->access_token;
        //获取失败
        if (!$token) {
            return false;
        }
        //返回新的TOKEN
        return $token;
    }

    /**
     * 构造一个菜单地址,使用鉴权链接
     * @param $controller string 控制器名称
     * @param $p1 string 具体应用的参数
     * @param $p2 string 具体应用的参数
     * @param $p3 string 具体应用的参数
     * @param $p4 string 具体应用的参数
     * @param $p5 string 具体应用的参数
     * @return string
     */
    public function transmitUrl($controller, $p1 = null, $p2 = null, $p3 = null, $p4 = null, $p5 = null, $p6 = null)
    {
        $url = urlencode(env('Application_redirect'));
        $state = implode('~', [$this->appid, $controller, $p1, $p2, $p3, $p4, $p5, $p6]);
        return $this->transmit($url, $state);
    }


    /**
     * 构造一个鉴权链接
     * @param $url string 跳转地址,不要带参数,没用,不会传递的
     * @param $state string 这个会传递给跳转地址
     * @return string
     */
    private function transmit($url, $state)
    {
        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->appId}&redirect_uri={$url}&response_type=code&scope=snsapi_userinfo&state={$state}#wechat_redirect";
    }


    /**
     * 根据Code获取用户的openid,在鉴权链接之后进行
     * @param $code
     * @return mixed
     */
    public function getUserByCode($code)
    {
        //测试的时候$code就是openid
        if(isset($_REQUEST['test'])) return $code;
        $appid = $this->appId;
        $secret = $this->appSecret;
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$secret}&code={$code}&grant_type=authorization_code";
        $ret = static::call($url);
        $wx = $this->userinfos($ret->openid,$ret->access_token);
        return $wx;
    }


    /*
     * 静默授权
     * */
    public function authorize($redirect_uri,$response_type,$scope)
    {
        $appid = $this->appId;
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appid}&redirect_uri={$redirect_uri}&response_type={$response_type}&scope={$scope}&state=STATE#wechat_redirect";
        $ret = static::call($url);
        return $ret;

    }

    /*
     * 静默授权2
     * */
    public function authorize2($code)
    {
        $appid = $this->appId;
        $secret = $this->appSecret;
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$secret}&code={$code}&grant_type=authorization_code";
        $ret = static::call($url);
        return $ret;

    }

    /**
     * 通常的调用微信接口,会自动更新Token
     * @param $url
     * @param string $postData
     * @param array $header
     * @return bool|mixed
     */
    public function call(string $url,$postData = null, array $header = array())
    {
        $net = (new Tools($url))->setHeader($header);
        $r =  json_decode($net->getOrPost($postData));
        //如果token超时需要再次调用
        if (self::weixin_invalide_access_token($r)) {
            $token = static::updateAccessToken();
            $url = preg_replace('/([\?&]access_token=)([^&]+)(&.*)?/ism', '$1' . $token . '$3', $url);
            //再次调用
            $r = json_decode($net->getOrPost($postData));
        }
        return $r;
    }

    public static function weixin_invalide_access_token($in)
    {
        return static::weixin_error($in, [40001, 40014, 42001]);
    }

    //判断是否返回指定的错误码
    public static function weixin_error($in, $errcodes = null)
    {
        if (!isset($in->errcode)) return false; //没有errcode

        if ($errcodes == null) return true;  //不指定code,有errcode 就返回true

        //单个
        if (!is_array($errcodes)) {
            if ($in->errcode == $errcodes) return true;
        } //array
        else {
            if (in_array($in->errcode, $errcodes)) return true;
        }
        return false;
    }


    /**
     * 发送消息
     * @param MsgSend $msg 消息对象
     * @return mixed
     */
    public function send(MsgSend $msg)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $this->getAccesToken();
        $ret = $this->call($url, $msg->toJson());
        $jsonRet = $ret;
        if (!empty($jsonRet->errcode) &&$jsonRet->errcode>0) {
            Log::info('微信发送错误',json_decode($ret,true));
        }
        return $ret;
    }


    /**
     * 发送图文消息 离线用模板消息
     * @param $openId
     * @param $title
     * @param $url
     * @param $description
     * @param $image
     * @return mixed
     */
     public function sendNews($openId, $title, $url, $description, $image)
    {
        $arr[]= new MsgSendNewsItem(
            $title,
            $description,
            $url,
            $image
        );
        $news = new MsgSendNews($openId,$arr);
        return  $this->send($news);

    }




    /**
     * @title:发送模版消息
     * @param $openId
     * @param $templateid
     * @param $url
     * @param $data
     * @return bool|mixed
     */
    public function sendtemplate($openId,$templateid,$url,$data)
    {
        $cont = json_encode([
            'touser' => $openId,
            'template_id' => $templateid,
            'url' => $url,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
        $sendurl = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $this->getAccesToken();
        $ret = $this->call($sendurl, $cont);
        if (!empty($ret->errcode) &&$ret->errcode>0) {
            Log::info('微信发送模板消息错误',$ret);
        }

        return $ret;
    }

    /**
     * 发送小程序消息
     * @param $openId
     * @param $title
     * @param $url
     * @param $description
     * @param $image
     * @return mixed
     */
    public function sendMiniprogramMessage($openId,$title,$appid,$pagepath,$media){
        $thumb_media_id = $media;
        $arr[]= new MsgSendMiniprogrampageItem(
            $title,
            $appid,
            $pagepath,
            $thumb_media_id
        );
        $Miniprogram = new MsgSendMiniprogram($openId,$arr);
        return  $this->send($Miniprogram);
    }


    /**
     * 设置公众号的菜单
     * @param array $menu
     * @return bool|mixed
     */
    public function menu($menu)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $this->getAccesToken();
        $menu = json_encode($menu, JSON_UNESCAPED_UNICODE);
        $r =  $this->call($url, $menu);
        return $r;
    }


    /**
     * @title:从微信获取一个用户的信息
     * @param $openId
     * @param $token
     * @return \SimpleXMLElement|mixed
     */
    public function userinfos($openId,$token)
    {
        //获取个人信息
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $token . "&openid=".$openId;
        $vsInfo = static::call($url);
        if (!$vsInfo) {
            return false;
        }
        //返回个人信息
        return $vsInfo;
    }

    /**
     * 从微信获取一个用户的信息
     * @param $openId
     * @return \SimpleXMLElement|bool
     */
    public function userinfo($openId)
    {
        //微信 API Token
        $token = $this->getAccesToken();
        //  获取个人信息
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $token . "&openid=$openId&lang=zh_CN";
        $vsInfo = static::call($url);
        if (!$vsInfo) {
            return false;
        }
        //返回个人信息
        return $vsInfo;
    }


    //获取用户列表
    public  function getUsers($nextopenid = null)
    {
        //微信 API Token
        $token = $this->getAccesToken();
        $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=".$token."&next_openid=".$nextopenid;
        $users = static::call($url);
        if (!$users) {
            return false;
        }
        //返回个人信息
        return $users;
    }

    /*生成二维码*/
    public function createQR($isLong=false){
        $codeId = static::_createEWMcodeId(time(),$isLong);
        $ewm = $this->qrCode($codeId, $isLong);
        if (empty($ewm)) {
            return false;
        }
        //根据id换取二维码图片
        $ticket =  $ewm->ticket;
        $ret2 = static::qrcodeDownload($ticket);
        return $ret2;
      //  return $this->createQRTmp($codeId, $codeId, $ewm);
    }


    private static function _createEWMcodeId($str, $isLong = false)
    {
        if ($isLong) {
            $randomStr = Tools::randomString(3);
            $codeId = $str . date('YmdHis', time()).$randomStr;
        } else {
            $codeId = '100001' . $str . date('ymdHis', time());
        }
        return $codeId;
    }

    /**
     * 从微信方获取二维码,永久二维码全部是字符型的
     * @param $scene_id string 场景ID
     * @param $permernent bool 是否永久
     * @return bool|mixed
     */
    public function qrCode($scene_id, $permernent)
    {
        $token = $this->getAccesToken();
        if ($permernent) {
            //永久
            $data = json_encode(array(
                'action_name' => 'QR_LIMIT_STR_SCENE',
                'action_info' => array(
                    'scene' => array(
                        'scene_str' => $scene_id,
                    )
                )
            ));
        } else {
            //临时
            $data = json_encode(array(
                'expire_seconds' => '2592000',
                'action_name' => 'QR_SCENE',
                'action_info' => array(
                    'scene' => array(
                        'scene_id' => $scene_id,
                    )
                )
            ));
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $token;
        $ret = $this->call($url, $data);

        return $ret;
    }


    /**
     * 从微信获取二维码图片
     * @param $ticket string 二维码的TICKET
     * @return mixed 图片内容
     */
    static function qrcodeDownload($ticket)
    {
        $url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($ticket);
        $net = (new Tools($url));
        return  $net->get();
    }

    //创建标签
    public function createTag($tag_name){
        $url = 'https://api.weixin.qq.com/cgi-bin/tags/create?access_token=' . $this->getAccesToken();
        $net = (new Tools($url));
        $data = json_encode(['tag'=>['name'=>$tag_name]]);
        return  $net->post($data);
    }

    /**
     * @title:上传资料
     * @param $type
     * @param $path
     * @return mixed
     * @author:maping
     * @Date: 2021/12/9
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function mediaUpload($type, $path){
        if (!file_exists($path) || !is_readable($path)) {
            throw new InvalidArgumentException(sprintf("File does not exist, or the file is unreadable: '%s'", $path));
        }

        if (!in_array($type, $this->allowTypes, true)) {
            throw new InvalidArgumentException(sprintf("Unsupported media type: '%s'", $type));
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/media/upload';
        return (new Tools($url))->httpUpload(['media' => $path], ['type' => $type,'access_token'=>$this->getAccesToken()]);
    }


    //批量为用户打标签
    public function batchtagging($openid_list,$tagid){
        $url = 'https://api.weixin.qq.com/cgi-bin/tags/members/batchtagging?access_token='.$this->getAccesToken();
        return (new Tools($url))->post(json_encode(['openid_list' => $openid_list,'tagid'=>$tagid]));
    }







}
