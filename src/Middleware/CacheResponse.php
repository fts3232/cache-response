<?php

namespace fts\CacheResponse\Middleware;

use Closure;
use fts\CacheResponse\Cache;
use Symfony\Component\HttpFoundation\Request;

class CacheResponse
{
    protected $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function handle(Request $request, Closure $next)
    {
        //如果缓存文件存在，返回缓存
        if ($html = $this->cache->exists($request)) {
            return $html;
        } else {
            $response = $next($request);
            //判断是否需要缓存
            if ($this->cache->shouldCache($request, $response)) {
                $this->cache->cache($request, $response);
            }
            return $response;
        }
    }
}
