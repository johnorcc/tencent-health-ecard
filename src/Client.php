<?php
/**
 * Created by PhpStorm.
 * User: johnor
 * Date: 2020/7/22
 * Time: 10:56
 */

namespace Tencent\ECard;


class Client
{

    private $requestParam;
    private $httpClient;

    /**
     * 定义许多的请求
     */
    const ACTION_REGISTER_HEALTH_CARD = "/ISVOpenObj/registerHealthCard";  //注册电子健康卡
    const ACTION_OCR_INFO = "/ISVOpenObj/ocrInfo"; //OCR身份证识别

    const ACTION_CARD_PACKAGE_ORDER = "/ISVOpenObj/getOrderIdByOutAppId"; //请求卡包订单
    const ACTION_AUTH_CODE_FOR_CARD = "/ISVOpenObj/getHealthCardByHealthCode"; //授权码换取电子健康卡
    const ACTION_BIND_CARD_RELATION = "/ISVOpenObj/bindCardRelation";//绑定院内卡
    const ACTION_REPORT_HIS_DATA = "/ISVOpenObj/reportHISData"; //用卡数据上报
    const ACTION_BATCH_REGISTER = "/ISVOpenObj/registerBatchHealthCard"; //批量注册
    const ACTION_UNIFORM_ORDER = "/ISVOpenObj/registerUniformOrder"; // 注册统一订单ID

    public function __construct(HttpClient $client)
    {
        $this->httpClient = $client;
        $this->requestParam = [
            'commonIn' => [],
            'req' => []
        ];
    }

    /**
     * 设置公共参数
     * @param array $data
     * @return Client
     */
    public function setCommonIn(array $data)
    {
        $this->requestParam['commonIn'] = $data;
        return $this;
    }

    /**
     * 添加公众参数
     * @param array $data
     * @return Client
     */
    public function addCommonIn(array $data)
    {
        $this->requestParam['commonIn'] = array_merge($this->requestParam['commonIn'], $data);
        return $this;
    }

    /**
     * 设置请求参数 | 复用请求必须使用setReq重置请求参数
     * @param array $data
     * @return Client
     */
    public function setReq(array $data)
    {
        $this->requestParam['req'] = $data;
        return $this;
    }


    /**
     * 设置请求参数
     * @param array $data
     * @return Client
     */
    public function addReq(array $data)
    {
        $this->requestParam['req'] = array_merge($this->requestParam['req'], $data);
        return $this;
    }

    /**
     * 发送请求获得返回值
     * @param $action
     * @return Response
     */
    public function send($action)
    {
        return $this->httpClient->post($this->requestParam, $action);
    }

}