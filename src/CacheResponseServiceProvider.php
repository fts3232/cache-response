<?php

namespace fts\CacheResponse;

use fts\CacheResponse\Console\ClearCache;
use fts\CacheResponse\Console\CreateCache;
use Illuminate\Support\ServiceProvider;

class CacheResponseServiceProvider extends ServiceProvider
{
    /**
     * 服务提供者加是否延迟加载.
     *
     * @var bool
     */
    protected $defer = true;

    public function register()
    {
        //添加清理缓存命令
        $this->commands(ClearCache::class);
        //添加创建缓存命令
        $this->commands(CreateCache::class);
        $this->app->singleton(Cache::class, function () {
            $instance = new Cache($this->app->make('files'));
            return $instance;
        });
    }

    /**
     * 获取由提供者提供的服务.
     *
     * @return array
     */
    public function provides()
    {
        return [Cache::class];
    }
}
