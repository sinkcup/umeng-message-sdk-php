<?php
require_once __DIR__ . '/vendor/autoload.php';
$conf = array(
    'appkey'            => '534ce13b56240b219b00106d', //按照友盟后台填写
    'app_master_secret' => 'j96jq7s8vfdt9ldhsrivjxkzbwezi4uv', //按照友盟后台填写
);

$o = new \Umeng\Message\Client($conf);
//广播
$data = array(
    'title' => '广播标题',
    'text' => '友盟测试：broadcastNotification 这是一条广播',
);
$r = $o->broadcastNotification($data);
var_dump($r);

//按省发通知
$data = array(
    'title' => '河北欢迎你',
    'text' => '燕郊的别野、驴肉的火烧，这是另一种生活方式',
    'province' => '河北',
);
$r = $o->sendLbsNotification($data);
var_dump($r);
 
//其他功能的使用方法：请参考 tests/
?>
