<?php
require_once __DIR__ . '/../autoload.php';
class MessageTest extends PHPUnit_Framework_TestCase
{
    private $conf = array(
        'appkey'            => '534ce13b56240b219b00106d', //按照友盟后台填写
        'app_master_secret' => 'j96jq7s8vfdt9ldhsrivjxkzbwezi4uv', //按照友盟后台填写
    );

    public function testSendLbsNotification()
    {
        echo __FUNCTION__ . "\n";
        $c = new \Umeng\Message($this->conf);
        $data = array(
            'title' => '上海欢迎你',
            'text' => '友盟测试：sendLbsNotification 生煎馒头、粢饭糕，欢迎品尝',
            'province' => '上海',
        );
        $r = $c->sendLbsNotification($data);
        $this->assertEquals(true, $r);
 
        $data = array(
            'title' => '北京欢迎你',
            'text' => '友盟测试：sendLbsNotification',
            'province' => '北京',
        );
        $r = $c->sendLbsNotification($data);
        $this->assertEquals(true, $r);
    }

    public function testSendNotificationToDevices()
    {
        echo __FUNCTION__ . "\n";
        $c = new \Umeng\Message($this->conf);
        $data = array(
            'title' => '发送给多个设备',
            'text' => '友盟测试：sendNotificationToDevices',
            'device_tokens' => array(
                'AsW2-KWYii5jnf8mEguOBYConmKTWsL3cVxhEwklaHZ6',
                'AhS_pRSGwOUQwk7ibK1iZsjF2YAwEynr5v9J_TUGP8kQ',
            ),
        );
        $r = $c->sendNotificationToDevices($data);
        $this->assertEquals(true, $r);
        
        $data = array(
            'title' => '发送给一个设备',
            'text' => '友盟测试：sendNotificationToDevices',
            'device_tokens' => 'AsW2-KWYii5jnf8mEguOBYConmKTWsL3cVxhEwklaHZ6',
        );
        $r = $c->sendNotificationToDevices($data);
        $this->assertEquals(true, $r);
    }

    public function testBroadcastNotification()
    {
        echo __FUNCTION__ . "\n";
        $c = new \Umeng\Message($this->conf);
        $data = array(
            'title' => '广播',
            'text' => '友盟测试：broadcastNotification 这是一条广播',
        );
        $r = $c->broadcastNotification($data);
        $this->assertEquals(true, $r);
    }

    public function testSend()
    {
        echo __FUNCTION__ . "\n";
        $c = new \Umeng\Message($this->conf);
        $data = array(
            'type' => 'broadcast',
            'payload' => array(
                'body' => array(
                    'title' => 'send 标题',
                    'text' => 'send 内容',
                )
            )
        );
        $r = $c->send($data);
        $this->assertEquals(true, $r);
    }
}
?>
