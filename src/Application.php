<?php
/**
 * 参考think-swoole2.0开发
 * author:xavier
 * email:49987958@qq.com
 */

namespace xavier\swoole;

use Swoole\Http\Request;
use Swoole\Http\Response;
use think\App;
use think\Error;
use think\exception\HttpException;
use think\Request as thinkRequest;
use think\Config;

/**
 * Swoole应用对象
 */
class Application extends App
{
    private static $swoole = null;

    /**
     * 处理Swoole请求
     * @access public
     * @param  \Swoole\Http\Request $request
     * @param  \Swoole\Http\Response $response
     * @param  void
     */
    public function swoole(Request $request, Response $response)
    {
        try {
            thinkRequest::destroy();
            ob_start();
            // 重置应用的开始时间和内存占用
            $this->beginTime = microtime(true);
            $this->beginMem  = memory_get_usage();

            $_COOKIE = $request->cookie ?: [];
            $_GET    = $request->get ?: [];
            $_POST   = $request->post ?: [];
            $_COOKIE = $request->cookie ?: [];
            $_FILES  = $request->files ?: [];
            $_SERVER = array_change_key_case($request->server ?: [], CASE_UPPER);

            $_SERVER['HTTP_HOST'] = Config::get('app_host') ? Config::get('app_host') : "127.0.0.1";
            $_SERVER['argv'][1] = $_SERVER["PATH_INFO"];
            $resp               = $this->run();
            $resp->send();
            $content = ob_get_clean();
            $status  = $resp->getCode();
            // 发送状态码
            $response->status($status);
            // 发送Header
            foreach ($resp->getHeader() as $key => $val) {
                $response->header($key, $val);
            }
            $response->end($content);
        } catch (HttpException $e) {
            $this->exception($response, $e);
        } catch (\Exception $e) {
            $this->exception($response, $e);
        } catch (\Throwable $e) {
            $this->exception($response, $e);
        }
    }

    protected function exception($response, $e)
    {
        if ($e instanceof \Exception) {
            $handler = Error::getExceptionHandler();
            $handler->report($e);

            $resp    = $handler->render($e);
            $content = $resp->getContent();
            $code    = $resp->getCode();

            $response->status($code);
            $response->end($content);
        } else {
            $response->status(500);
            $response->end($e->getMessage());
        }

        throw $e;
    }

    public function setSwoole($swoole)
    {
        self::$swoole = $swoole;
    }

    public static function getSwoole()
    {
        return self::$swoole;
    }
}
