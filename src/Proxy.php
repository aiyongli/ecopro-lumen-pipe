<?php

namespace Ecopro\Pipe;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;

class Proxy
{
    use ForwardsCalls;

    private $_passable;
    private $_pipes = [];

    /**
     * @param object $passable
     */
    public function __construct($passable)
    {
        $this->_passable = $passable;
    }

    /**
     * 清除管道载体与管理处理闭包
     * @return static
     */
    public function pipeClear()
    {
        $this->_pipes = [];

        return $this;
    }

    /**
     * 增加自定义闭包
     * @return static
     */
    public function pipe(callable $callable)
    {
        $this->_pipes[] = function($passable, callable $next) use ($callable) {
            return call_user_func($callable, $passable, $next);
        };

        return $this;
    }

    /**
     * 使用管道执行目标方法
     */
    public function __call($method, $arguments)
    {
        if((method_exists($this->_passable, $method) || method_exists($this->_passable, '__call'))) {
            if(empty($this->_pipes) || Str::startsWith($method, 'pipe')) {
                // 直接调用管道方法
                return $this->forwardCallTo($this->_passable, $method, $arguments);
            }

            return (new Pipeline())->send($this->_passable)->through($this->_pipes)->then(function() use ($method, $arguments) {
                return $this->forwardCallTo($this->_passable, $method, $arguments);
            });
        } else {
            throw new \Exception(sprintf(
                'Call to undefined method %s::%s()', get_class($this->_passable), $method
            ));
        }
    }
}
