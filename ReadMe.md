### 基于腾讯居民电子健康卡开放平台对接封装sdk

本sdk基于开放平台接口列表分装方便调用和判断返回结果 [原始文档地址](https://open.tengmed.com/doc/)

初始化及调用方法，项目基于symfony，所以配置service.yml即可使用，框架本身处理了依赖注入，如果是symfony3需配置

    ecard_gz_client:
        class:      GuzzleHttp\Client
    ecard_http_client:
        class:      Tencent\ECard\HttpClient
        arguments:  ['@ecard_gz_client','@logger','%ecard.app_id%','%ecard.app_secret%','%ecard.channel%','%ecard.hospital_id%','%ecard.auth_url%','%ecard.base_url%']
    ecard_client:
        class:      Tencent\ECard\Client
        arguments:  ['@ecard_http_client']
    
  
其中 Tencent\ECard\HttpClient 传参参数

    ClientInterface $client  GuzzleHttp\Client 对象
    LoggerInterface $logger logger对象
    $appID  开放平台入驻成功的appid
    $appSecret 开放平台入驻成功的appSecret
    $channel  请求渠道 0为微信服务号
    $hospitalID 医院入驻并已激活的医院id
    $authUrl  中控服务获取token的get地址
    $baseUrl  基础接口地址 https://p-healthopen.tengmed.com/rest/auth/HealthCard/HealthOpenPlatform

**如果** 没使用依赖注入型框架需手动处理对象实例化问题