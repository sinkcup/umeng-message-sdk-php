<?php
namespace Umeng\Message;
/**
 * 友盟消息推送
 * @author   sinkcup <sinkcup@163.com>
 */

class Client
{
    protected $conf = array(
        'api_uri_prefix'    => 'http://msg.umeng.com/',
        'appkey'            => '', //应该用app_key，但友盟用了不规范的appkey，为了保持一致，只好这样。
        'app_master_secret' => '',
    );

    public function __construct($conf=array())
    {
        $this->conf = array_merge($this->conf, $conf);
        if(empty($this->conf['appkey']) || empty($this->conf['app_master_secret'])) {
            throw new Exception('need conf: appkey and app_master_secret');
        }
    }
    
    /**
     * 生成token。用于安全校验。
     */
    private function grantToken()
    {
        $now = time();
        return array(
            'appkey' => $this->conf['appkey'],
            'timestamp' => $now,
            'validation_token' => md5($this->conf['appkey'] . $this->conf['app_master_secret'] . $now),
        );
    }

    /**
     * 按位置发送通知。目前只支持省，不支持市县镇。
     *
     * @param array $data array(
            'title' => '标题asdf',
            'text' => '内容asdf',
            'province' => '河北',
        )
     * @return boolean
     */
    public function sendLbsNotification($data)
    {
        $newData = array(
            'type' => 'groupcast',
            'payload' => array(
                'body' => array(
                    'title' => $data['title'],
                    'text' => $data['text'],
                )
            ),
            'filter' => array(
                'where' => array(
                    'and' => array(
                        array(
                            'province' => $data['province']
                        ),
                    ),
                ),
            )
        );
        return $this->send($newData);
    }
    
    /**
     * 广播通知
     * @param array $data array(
            'title' => '标题asdf',
            'text' => '内容asdf',
        )
     * @return boolean
     */
    public function broadcastNotification($data)
    {
        $newData = array(
            'type' => 'broadcast',
            'payload' => array(
                'body' => array(
                    'title' => $data['title'],
                    'text' => $data['text'],
                )
            )
        );
        return $this->send($newData);
    }

    /**
     * 发送通知到指定设备
     * @param array $data array(
            'title' => '标题asdf',
            'text' => '内容asdf',
            'device_tokens' => 'asdf,qwer,zxcv'
        )
     * @return boolean
     */
    public function sendNotificationToDevices($data)
    {
        if(!isset($data['device_tokens']) || empty($data['device_tokens'])) {
            throw new Exception('need param: device_tokens');
        }
        $newData = array(
            'type' => 'listcast',
            'payload' => array(
                'body' => array(
                    'title' => $data['title'],
                    'text' => $data['text'],
                )
            ),
        );
        if(is_array($data['device_tokens'])) {
            $newData['device_tokens'] = implode(',', $data['device_tokens']);
        } else {
            $newData['device_tokens'] = $data['device_tokens'];
        }

        return $this->send($newData);
    }

    /**
     * 发送notification或message。请阅读友盟文档。
     */
    public function send($data)
    {
        $token = $this->grantToken();
        $newData = $data;
        foreach($token as $k=>$v) {
            $newData[$k] = $v;
        }

        //必填 消息发送类型,其值为unicast,listcast,broadcast,groupcast或customizedcast
        if(!isset($data['type'])) {
            throw new Exception('need param: type');
        }

        // 可选 当type=customizedcast时,开发者填写自己的alias,友盟根据alias进行反查找,得到对应的device_token。多个alias时用英文逗号分,不能超过50个。
        if(isset($data['alias']) && !empty($data['alias'])) {
            if(is_array($data['alias'])) {
                $newData['alias'] = implode(',', $data['alias']);
            } else {
                $newData['alias'] = $data['alias'];
            }
        }

        // 必填 消息类型，值为notification或者message
        if(!isset($data['payload']['display_type'])) {
            $newData['payload']['display_type'] = 'notification';
        }

        // 必填 通知栏提示文字。但实际没有用，todo确认
        if(!isset($data['payload']['body']['ticker'])) {
            $newData['payload']['body']['ticker'] = $data['payload']['body']['title'];
        }

        //可选 消息描述。用于友盟推送web管理后台，便于查看。
        if(!isset($data['description'])) {
            $newData['description'] = $data['payload']['body']['title'];
        }
        
        $defaultTrueParams = array(
            // 通知到达设备后的提醒方式
            'play_vibrate', // 可选 收到通知是否震动,默认为"true".注意，"true/false"为字符串
            'play_lights',  // 可选 收到通知是否闪灯,默认为"true"
            'play_sound',   // 可选 收到通知是否发出声音,默认为"true"
        );

        foreach($defaultTrueParams as $one) {
            if(isset($data['payload']['body'][$one]) && ($data['payload']['body'][$one] == false || $data['payload']['body'][$one] == 'false')) {
                $newData['payload']['body'][$one] = 'false';
            }
        }

        $http = new \HTTPRequest($this->conf['api_uri_prefix'] . 'api/send', HTTP_METH_POST);
        $http->setBody(json_encode($newData));
        $http->send();
        $body = $http->getResponseBody();
        if($http->getResponseCode() != 200) {
            throw new Exception($body);
        }
        $tmp = json_decode($body, true);

        if(!isset($tmp['ret']) || $tmp['ret'] != 'SUCCESS') {
            throw new Exception($body);
        }
        return true;
    }
}
?>
