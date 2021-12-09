<?php
namespace MaPing\Wechat;
use GuzzleHttp\Exception\GuzzleException;

/**
 * 封装网络请求,主要是CURL
 */
class Tools
{
    /**@var string 本次请求的地址 */
    private $url;

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * 构造方法,记录请求地址
     * @param $url string
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    /**
     * 超时时间设置
     * @var int
     */
    private $timeout = 60;

    /**
     * 设置超时时间
     * @param $seconds int
     * @return $this
     */
    public function setTimeout($seconds)
    {
        $this->timeout = $seconds;
        return $this;
    }

    /**
     * 请求头参数数组
     * @var array
     */
    private $header;

    /**
     * 设置请求头参数 数组
     * @param array $header
     * @return $this
     */
    public function setHeader(array $header)
    {
        $this->header = $header;
        return $this;
    }

    /**
     * SSL 证书
     * @var string
     */
    private $ssl_cert, $ssl_key;

    /**
     * 设置 SSL 证书
     * @param $cert string
     * @param $key string
     * @return $this
     */
    public function setSSL($cert, $key)
    {
        $this->ssl_cert = $cert;
        $this->ssl_key = $key;
        return $this;
    }

    /**
     * 常用的请求
     * @param $url string
     * @param array $params
     * @return mixed
     */
    public static function curl($url, $params = [])
    {
        $net = new self($url);
        return $net->getOrPost($params);
    }

    /**
     * 发起GET请求
     * @return mixed
     */
    public function get()
    {
        return $this->getOrPost();
    }

    /**
     * 发起POST请求
     * @param $params string|array 参数 数组或字符串
     * @return mixed
     */
    public function post($params)
    {
        return $this->getOrPost($params);
    }


    /**
     * 发起CURL请求
     * @param array|string $params
     * @return mixed
     */
    public function getOrPost($params = null)
    {
        $options = [
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_NOBODY => 0,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 5.1; rv:21.0) Gecko/20100101 Firefox/21.0',
            CURLOPT_URL => $this->url
        ];
        if ($this->header) {
            $options[CURLOPT_HTTPHEADER] = $this->header;
        }
        if ($params) {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $params;
        } else {
            $options[CURLOPT_HTTPGET] = true;
        }
        if ($this->ssl_cert) {
            $options[CURLOPT_SSLCERT] = $this->ssl_cert;
            $options[CURLOPT_SSLKEY] = $this->ssl_key;
        }
        //创建CURL请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        assert($ch !== false);
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        curl_close($ch);
        //记录请求时间日志
        return $result;
    }


    /**
     * 生成指定长度的随机字符串(大写,小写,数字)
     * @param int $length
     * @return string
     */
    public static function randomString($length = 32)
    {
        // Create token to login with
        $t1 = '';
        $string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

        for ($i = 0; $i < 30; $i++) {
            $j = rand(0, 61);
            $t1 .= $string[$j];
        }

        return $t1;
    }

    /**
     * Upload file.
     *
     * @param string $url
     * @param array  $files
     * @param array  $form
     * @param array  $query
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function httpUpload( array $files = [], array $form = [], array $query = [])
    {
        $multipart = [];
        foreach ($files as $name => $path) {
            $multipart[] = [
                'name' => $name,
                'contents' => fopen($path, 'r'),
            ];
        }

        foreach ($form as $name => $contents) {
            $multipart[] = compact('name', 'contents');
        }
        $params = ['query' => $query, 'multipart' => $multipart, 'connect_timeout' => 30, 'timeout' => 30, 'read_timeout' => 30];
        return $this->postNew($params);
    }

    /**
     * @title:POST请求
     * @param $body
     * @param $apiStr
     * @return array|bool
     * @author:maping
     * @Date: 2021/9/15
     */
    public function postNew($params)
    {
        $url = $this->url;
        $client = new \GuzzleHttp\Client();
        try {
            $res  = $client->post($url, $params);
            $data = $res->getBody()->getContents();
            return json_decode($data,true);
        } catch (GuzzleException $e) {
            return [];
        }
    }

}

