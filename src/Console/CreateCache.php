<?php

namespace fts\CacheResponse\Console;

use fts\CacheResponse\Cache;
use Illuminate\Console\Command;

/**
 * 生成缓存类
 *
 * Class CreateCache
 * @package fts\CacheResponse\Console
 */
class CreateCache extends Command
{
    /**
     * 命令名称 uri 可选项 可多个
     *
     * @var string
     */
    protected $signature = 'page-cache:create {uri?* : URL  of page to create}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = 'Create the page cache.';

    /**
     * 执行命令逻辑
     *
     * @return void
     */
    public function handle()
    {
        $cache = $this->laravel->make(Cache::class);

        $uris = $this->argument('uri');
        $time = date('Y/m/d H:i:s', time());
        //判断是否指定了特定的缓存页面和特定的目录，不是生成所有页面缓存
        if (empty($uris)) {
            $uris = $this->getShouldCacheRoute();
            $this->info("[{$time}]: 生成所有页面缓存");
        }
        foreach ($uris as $uri) {
            $this->create($cache, $uri);
        }
    }

    /**
     * 获取所有应该缓存的路由
     *
     * @return array
     */
    protected function getShouldCacheRoute()
    {
        $routes = $this->laravel->routes->getRoutes();
        $return = array();
        foreach ($routes as $route) {
            $middleware = $route->action['middleware'];
            if ((is_array($middleware) && in_array('cache', $middleware)) || $middleware == 'cache') {
                $return[] = $route->uri;
            }
        }
        return $return;
    }

    /**
     *  生成缓存文件
     *
     * @param Cache  $cache cache类
     * @param string $uri   要生成缓存的uri
     */
    protected function create(Cache $cache, $uri)
    {
        $time = date('Y/m/d H:i:s', time());
        if ($cache->createCache($uri)) {
            $this->info("[{$time}]: {$uri} 页面缓存生成成功");
        } else {
            $this->warn("[{$time}]: {$uri} 页面缓存生成失败");
        }
    }
}
