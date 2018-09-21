<?php
// +----------------------------------------------------------------------
// | Config
// +----------------------------------------------------------------------
// | Author: 橙加冰
// +----------------------------------------------------------------------
// | Date: 2018/9/21 13:53
// +----------------------------------------------------------------------

namespace xavier\swoole\command;


use think\console\Command;
use think\console\Input;
use think\console\Output;

class Config  extends Command
{
    public function configure()
    {
        $this->setName('swoole:config')
            ->setDescription('Loading Swoole Server Config for ThinkPHP');
    }

    protected function execute(Input $input, Output $output)
    {
        $conf_arr = ['swoole', 'swoole_server'];
        foreach ($conf_arr as $config) {
            // 读取扩展配置文件
            list($bool, $file) = $this->path_exists($config);
            if (!$bool) {
                if (copy(__DIR__ . '/../config/' . $config . CONF_EXT, $file)) {
                    $this->output->writeln('生成配置' . $file . '文件成功');
                } else {
                    throw new \Exception('生成配置文件有误~');
                }
            } else {
                $this->output->writeln('配置文件已经存在');
            }
        }

    }

    public function path_exists($file_config)
    {
        $file = CONF_PATH . 'extra' . DS . $file_config . CONF_EXT;
        if (file_exists($file)) {
            return [true, $file];
        }
        return [false, $file];
    }


}