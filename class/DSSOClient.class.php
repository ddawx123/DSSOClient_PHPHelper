<?php
/**
 * Single Sign On Client Helper
 * 单点登录客户端辅助类
 * @package DingStudio/SSO
 * @subpackage SSO/PHPClient
 * @author David Ding
 * @copyright 2012-2020 DingStudio Technology All Rights Reserved
 */
class DSSOClient {

    protected $_IDPInfo = array(); //服务端信息集合
    protected $_ClientInfo = array(); //客户端信息集合

    /**
     * 初始化客户端
     * @param string $hostname IDP主机名
     * @param integer $port IDP端口
     * @param string $scheme IDP访问协议
     * @param string $timezone 通信时区（仅海外服务器需修改）
     */
    public function __construct($hostname, $port = 443, $scheme = 'https', $timezone = 'Asia/Shanghai') {
        $this->_IDPInfo['HOST'] = $hostname;
        $this->_IDPInfo['PORT'] = $port;
        $this->_IDPInfo['SCHEME'] = $scheme;
        date_default_timezone_set($timezone);
        $this->_ClientInfo['timezone'] = $timezone;
    }

    /**
     * 触发认证操作，按需自主实现重定向或资料换取
     * @param string $dataType 数据处理方式（缺省JSON，亦可XML但需要自行数据解析）
     * @return mixed
     */
    public function authorize($dataType = 'json') {
        if (empty($this->_ClientInfo['appid']) || empty($this->_ClientInfo['service'])) die('Please call config function to set client information first.');
        if (empty($_GET['token'])) {
            http_response_code(307);
            header('Location: '.$this->_IDPInfo['SCHEME'].'://'.$this->_IDPInfo['HOST'].':'.$this->_IDPInfo['PORT'].'/sso/login?returnUrl='.base64_encode(urlencode($this->_ClientInfo['service'])));
        } else {
            return $this->getUserInfo(htmlspecialchars($_GET['token']), $dataType);
        }
    }

    /**
     * 配置客户端
     * @param string $redirect_uri 回调地址
     * @param string $appId 客户端全局唯一标识
     * @param string $secretKey 接入客户通信密钥（按需可选）
     */
    public function config($redirect_uri, $appId, $secretKey = null) {
        $this->_ClientInfo['service'] = $redirect_uri;
        $this->_ClientInfo['appid'] = $appId;
        if (!is_null($secretKey)) $this->_ClientInfo['sid'] = $secretKey;
    }
    
    /**
     * 通过资源令牌获取用户资料
     * @param string $access_token 资源访问令牌
     * @param string $format 自定义数据格式（缺省json，支持xml，一般无需更改）
     * @return object
     */
    protected function getUserInfo($access_token, $format = 'json') {
        if (empty($this->_ClientInfo['appid']) || empty($this->_ClientInfo['service'])) return null;
        if (empty($this->_ClientInfo['sid'])) {
            $body = array(
                'action'    =>  'verify',
                'appid' =>  $this->_ClientInfo['appid'],
                'format'    =>  $format,
                'token' =>  $access_token,
                'reqtime'   =>  time()
            );
        } else {
            $body = array(
                'action'    =>  'verify',
                'appid' =>  $this->_ClientInfo['appid'],
                'format'    =>  $format,
                'sid'   =>  $this->_ClientInfo['sid'],
                'token' =>  $access_token,
                'reqtime'   =>  time()
            );
        }
        $retString = $this->webAccessRequest($this->_IDPInfo['SCHEME'].'://'.$this->_IDPInfo['HOST'].':'.$this->_IDPInfo['PORT'].'/api', $body);
        if ($format == 'json') {
            $data = json_decode($retString, true);
            switch ($data['code']) {
                case 200:
                    return array('status'=>'ok','data'=>$data['data']);
                    break;
                default:
                    //errId说明：
                    //201：账户密码过期需修改，400：APPID或密钥校验失败，401：当前账号无权进入此应用
                    //403：token过期，405：操作超时，50x：IDP服务器故障
                    return array('status'=>'fail','errId'=>$data['code'],'msg'=>$data['message']);
                    break;
            }
        } else {
            return simplexml_load_string($retString, 'SimpleXMLElement', LIBXML_NOCDATA);
        }
    }

    /**
     * Web访问请求方法封装（RAWPost方式）
     * @param string $url 目标地址
     * @param array $param 传送数据体
     * @return string
     */
    private function webAccessRequest($url, $param) {
        $param = json_encode($param);
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_HEADER  =>  0,
            CURLOPT_URL =>  $url,
            CURLOPT_USERAGENT   =>  'DSSOClient/2.0_PHP_'.PHP_OS,
            CURLOPT_SSL_VERIFYPEER  =>  false,
            CURLOPT_SSL_VERIFYHOST  =>  false,
            CURLOPT_RETURNTRANSFER  =>  true,
            CURLOPT_POST    =>  true,
            CURLOPT_HTTPHEADER  =>  array('Content-Type: application/json; charset=UTF-8'),
            CURLOPT_POSTFIELDS  =>  $param,
            CURLOPT_FOLLOWLOCATION  =>  true
        ));
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            curl_close($ch);
            return null;
        } else {
            curl_close($ch);
            return $result;
        }
    }
}