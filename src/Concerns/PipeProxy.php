<?php
namespace Ecopro\Pipe\Concerns;

use Ecopro\Pipe\Proxy;
use Ecopro\Pipe\Invokers\PipeExceptionInvoker;
use Ecopro\Pipe\Invokers\PipeTransactionInvoker;

trait PipeProxy
{
    /**
     * @var Proxy
     */
    protected $_pipeproxy;

    /**
     * @return Proxy
     */
    protected function pipeInit()
    {
        if(!isset($this->_pipeproxy)) {
            $this->_pipeproxy = new Proxy($this);
        }

        return $this->_pipeproxy;
    }

    /**
     * @return static
     */
    public function pipeClear()
    {
        return $this->pipeInit()->pipeClear();
    }

    /**
     * @return static
     */
    public function pipeCall(callable $callable)
    {
        return $this->pipeInit()->pipe($callable);
    }

    /**
     * @return static
     */
    public function pipeBefore(callable $callable)
    {
        return $this->pipeInit()->pipe(function($passable, $next) use ($callable) {
            call_user_func($callable, $passable);
            return $next($passable);
        });
    }

    /**
     * @return static
     */
    public function pipeAfter(callable $callable)
    {
        return $this->pipeInit()->pipe(function($passable, $next) use ($callable) {
            $ret = $next($passable);
            call_user_func($callable, $passable, $ret);
            return $ret;
        });
    }

    /**
     * 使用异常捕获
     * @param callable $handler
     * @return static
     */
    public function pipeCatch(callable $handler)
    {
        $resolver = (new PipeExceptionInvoker)->registerExceptionHandlerResolver(function() use($handler) {
            return $handler;
        });

        return $this->pipeInit()->pipe($resolver);
    }

    /**
     * 使用事务
     * @return static
     */
    public function pipeTransaction(?callable $handler = null)
    {
        $resolver = new PipeTransactionInvoker();
        if($handler) {
            $resolver->registerExceptionHandlerResolver(function() use($handler) {
                return $handler;
            });
        }

        return $this->pipeInit()->pipe($resolver);
    }
}
