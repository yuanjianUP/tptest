<?php
declare (strict_types = 1);

namespace app\command;

use Pheanstalk\Pheanstalk;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\Db;

class Consumer extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('consumer')
            ->setDescription('the consumer command');
    }

    protected function execute(Input $input, Output $output)
    {
        $conn = Pheanstalk::create('127.0.0.1', 11300,10);
        $conn->useTube('test');
        while (1){
            try{
                $job = $conn->reserveWithTimeout(10);
                if($job === null){
                    throw new Exception('超时了');
                }
                $data = json_decode($job->getData(),true);
                Db::table('order')->where(['id'=>$data['orderId']])->update(['status'=>1]);
                $conn->delete($job);
            }catch (\Exception $e){
                echo $e->getMessage();
            }
        }
    }
}
