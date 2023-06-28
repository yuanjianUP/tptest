<?php
declare (strict_types = 1);

namespace app\command;

use Pheanstalk\Pheanstalk;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Db;
use think\Exception;

class Consumer extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('consumer')
            ->setDescription('消费者');
    }

    protected function execute(Input $input, Output $output)
    {
        $conn = Pheanstalk::create('127.0.0.1', 11300,10);
        $conn->useTube('test');
        while (1){
            try{
                $job = $conn->reserve();
                if($job === null){
                    throw new Exception('超时了');
                }
                $data = json_decode($job->getData(),true);
                Db::table('order')->where(['id'=>$data['orderId']])->update(['status'=>1,'utime'=>date('Y-m-d H:i:s')]);
                $conn->delete($job);
            }catch (\Exception $e){
                echo $e->getMessage();
            }
        }
    }
}
