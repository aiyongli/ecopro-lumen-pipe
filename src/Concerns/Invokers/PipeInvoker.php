<?php
namespace Ecopro\Pipe\Concerns\Invokers;

trait PipeInvoker
{
    public function __invoke($passable, $next)
    {
        if(is_callable($next)) {
            return $this->handle($passable, $next);
        }

        throw new \Exception("Incoming parameter \$next must callable!");
    }

    /**
     * 处理
     * @param mixed $passable
     * @param callable $next
     */
    public abstract function handle($passable, callable $next);
}
