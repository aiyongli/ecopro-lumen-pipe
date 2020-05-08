<?php
namespace Ecopro\Pipe\Concerns\Invokers;

use Illuminate\Support\Facades\DB;

/**
 * 事务处理（包含异常处理回调功能）
 */
trait PipeTransaction
{
    use PipeException {
        PipeException::handle as exceptionHandle;
    }

    /**
     * 处理
     * @param mixed $passable
     * @param callable $next
     */
    public function handle($passable, callable $next)
    {
        return $this->exceptionHandle($passable, function($passable) use($next) {
            return DB::transaction(function () use($passable, $next) {
                return $next($passable);
            });
        });
    }
}
