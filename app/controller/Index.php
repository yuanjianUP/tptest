<?php
namespace app\controller;

use app\BaseController;
use Pheanstalk\Pheanstalk;
use think\Exception;
use think\facade\Db;

class Index extends BaseController
{
    public function index()
    {
        //创建订单
        $orderId = Db::table('order')->insert([
            'orderNo' => time(),
            'status' => 0,
            'ctime' => time(),
        ]);
        $conn = Pheanstalk::create('127.0.0.1', 11300,10);
        //选择tube
        $conn->useTube('test');
        //发布延时任务
        $conn->put(json_encode(['orderId' => $orderId,'ctime' => time()]),1024,10);
        return 'is add';
    }

    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }
}
