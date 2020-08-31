<?php
/**
 * Created by PhpStorm.
 * User: johnor
 * Date: 2020/7/22
 * Time: 10:57
 */

namespace Tencent\ECard;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

class HttpClient
{

    private $httpClient;
    private $log;
    private $appToken = null;
    private $appID;
    private $appSecret;
    private $channelNum;
    private $hospitalID;

    private $tokenAuthURL;
    private $baseUrl;

    /**
     * 构造函数
     * HttpClient constructor.
     * @param ClientInterface $client
     * @param LoggerInterface $logger
     * @param $appID
     * @param $appSecret
     * @param $channel
     * @param $hospitalID
     * @param $authUrl
     * @param $baseUrl
     */
    public function __construct(ClientInterface $client,LoggerInterface $logger, $appID, $appSecret, $channel, $hospitalID, $authUrl, $baseUrl)
    {
        $this->httpClient = $client;
        $this->log = $logger;
        $this->appID = $appID;
        $this->appSecret = $appSecret;
        $this->channelNum = $channel;
        $this->hospitalID = $hospitalID;
        $this->tokenAuthURL = $authUrl;
        $this->baseUrl = $baseUrl;
    }

    /**
     * 获取签名
     * @param array $param
     * @return string
     */
    private function getSign(array $param)
    {
        ksort($param);
        $str = '';
        $i = 0;
        foreach ($param as $k => $v) {
            if ($i == 0) {
                $str = $k . '=' . $v;
            } else {
                $str .= '&' . $k . '=' . $v;
            }
            $i++;
        }
        $sha256 = hash('sha256', $str . $this->appSecret, true);
        $base64 = base64_encode($sha256);
        return $base64;
    }


    /**
     * 生成UUID
     * @param string $prefix
     * @return string
     */
    function create_uuid($prefix = "")
    {    //可以指定前缀
        if (function_exists("uuid_create")) {
            $uuid = uuid_create();
        } else {
            $data = openssl_random_pseudo_bytes(16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
            $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }
        return $prefix . $uuid;
    }


    /**
     * 发送请求
     * appToken 每次都是从http获取
     * @param $requestParam
     * @param $action
     * @return Response
     */
    public function post($requestParam, $action)
    {
        if ($this->appToken === null) {
            //如果实例化期间Token为null 需要先请求Token 中控服务器获取
            try {
                $res = $this->httpClient->request("GET", $this->tokenAuthURL);
                $resp = $res->getBody()->getContents();
                $data = json_decode($resp,true);
                if (isset($data['code']) && $data['code'] === 0) {
                    $this->appToken = $data['message'];
                } else {
                    $res = $this->httpClient->request("GET", $this->tokenAuthURL);
                    $resp = $res->getBody()->getContents();
                    $data = json_decode($resp,true);
                    if (isset($data['code']) && $data['code'] === 0) {
                        $this->appToken = $data['message'];
                    } else {
                        return new Response([
                            'commonOut' => [
                                'requestId' => $this->create_uuid("e_card"),
                                'resultCode' => 22222,
                                'errMsg' => $data['message']
                            ],
                            'rsp' => []
                        ]);
                    }
                }
            } catch (GuzzleException $e) {
                return new Response([
                    'commonOut' => [
                        'requestId' => $this->create_uuid("e_card"),
                        'resultCode' => 22222,
                        'errMsg' => "获取appToken失败"
                    ],
                    'rsp' => []
                ]);
            }
        }
        $commonIn = $requestParam['commonIn'];
        $commonIn['requestId'] = $this->create_uuid("e_card");
        $commonIn['timestamp'] = strval(time());
        $commonIn['appToken'] = $this->appToken;
        $commonIn['hospitalId'] = $this->hospitalID;
        $commonIn['channelNum'] = $this->channelNum;
        $req = $requestParam['req'];
        $req = array_filter($req,function ($var){
            return $var !== '';
        }); //去除空值 空字符串等不然签名会有问题
        $joinSignArr = array_merge($commonIn, $req);
        $commonIn['sign'] = $this->getSign($joinSignArr);
        try {
            $url = $this->baseUrl . $action;
            $reqBody = [
                'commonIn' => $commonIn,
                'req' => $req
            ];
            $this->log->info("电子健康卡请求",[
                'url' => $url,
                'reqBody' => $reqBody
            ]);
            $res = $this->httpClient->request("POST", $url , ['json' => $reqBody]);
            $resp = $res->getBody()->getContents();
            $this->log->info("电子健康卡返回",[
                'url' => $url,
                'respBody' => $resp
            ]);
            $data = json_decode($resp,true);
            return new Response($data);
        } catch (GuzzleException $e) {
        }
        return new Response([
            'commonOut' => [
                'requestId' => $commonIn['requestId'],
                'resultCode' => 22222,
                'errMsg' => "请求接口失败"
            ],
            'rsp' => []
        ]);
    }
}