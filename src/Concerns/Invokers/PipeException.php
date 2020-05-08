<?php
namespace Ecopro\Pipe\Concerns\Invokers;

/**
 * 异常处理
 */
trait PipeException
{
    use PipeInvoker;

    /**
     * @var callable
     */
    protected $_exceptionHandlerResolver;

    /**
     * 注册事务异常处理回调
     * @return static
     */
    public function registerExceptionHandlerResolver(callable $closure)
    {
        $this->_exceptionHandlerResolver = $closure;

        return $this;
    }

    /**
     * 注册默认事务异常处理回调
     * @return static
     */
    public function registerDefaultExceptionHandlerResolver()
    {
        $closure = function() {
            return function($e) {
                throw $e;
            };
        };

        return $this->registerExceptionHandlerResolver($closure);
    }

    /**
     * 获取异常处理回调
     * @return callable
     */
    public function resolveExceptionHandler()
    {
        if(empty($this->_exceptionHandlerResolver)) {
            $this->registerDefaultExceptionHandlerResolver();
        }

        return value($this->_exceptionHandlerResolver);
    }

    /**
     * 处理
     * @param mixed $passable
     * @param callable $next
     */
    public function handle($passable, callable $next)
    {
        try {
            return $next($passable);
        } catch(\Exception $e) {
            $handler = $this->resolveExceptionHandler();
            if(!is_callable($handler)) {
                throw new \Exception('Exception handler is not callable!', 0, $e);
            }
            return $handler($e);
        }
    }
}
