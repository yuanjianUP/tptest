<?php
namespace app\controller;

use app\BaseController;
use Pheanstalk\Pheanstalk;
use think\Db;
use think\Exception;

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
    //消费者
    public function consumer()
    {
        $conn = Pheanstalk::create('127.0.0.1', 11300,10);
        $conn->useTube('test');
        try{
            $job = $conn->reserveWithTimeout(10);
            if($job === null){
                throw new Exception('超时了');
            }
            sleep(60);
        }catch (\Pheanstalk\Exception\ConnectionException $e){
            echo $e->getMessage();
        }catch (\Exception $e){
            echo $e->getMessage();
        }
        print_r($job);
    }

    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }
}
