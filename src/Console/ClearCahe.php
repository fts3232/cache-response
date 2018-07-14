<?php

namespace fts\CacheResponse\Console;

use fts\CacheResponse\Cache;
use Illuminate\Console\Command;

/**
 * 清理缓存类
 *
 * Class ClearCache
 * @package fts\CacheResponse\Console
 */
class ClearCache extends Command
{
    /**
     * 命令名称 uri 可选项 可多个
     *
     * @var string
     */
    protected $signature = 'page-cache:clear {slug? : URL slug of page to delete}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = 'Clear the page cache.';

    /**
     * 执行命令逻辑
     *
     * @return void
     */
    public function handle()
    {
        $cache = $this->laravel->make(Cache::class);
        $slug = $this->argument('slug');
        //判断是否指定了特定的缓存页面和特定的目录，不是清空所有
        if (strpos($slug, '.html') !== false) {
            $this->forget($cache, $slug);
        } else {
            $this->clear($cache, $slug);
        }
    }

    /**
     * 删除缓存文件
     *
     * @param  fts\CacheResponse\Cache $cache cache类
     * @param  string                  $slug  指定的缓存名称
     * @return void
     */
    protected function forget(Cache $cache, $slug)
    {
        $time = date('Y/m/d H:i:s', time());
        if ($cache->forget($slug)) {
            $this->info("[{$time}]: \"{$slug}\" cache文件删除成功");
        } else {
            $this->info("[{$time}]: 没有 \"{$slug}\" 的cache文件");
        }
    }

    /**
     * 删除目录下所有缓存文件
     *
     * @param  fts\CacheResponse\Cache $cache cache类
     * @param  string                  $slug  指定的缓存名称
     * @return void
     */
    protected function clear(Cache $cache, $slug = '')
    {
        $time = date('Y/m/d H:i:s', time());
        if ($cache->clear($slug)) {
            $this->info("[{$time}]: " . $cache->getCachePath($slug) . ' 清空成功');
        } else {
            $this->warn("[{$time}]: " . $cache->getCachePath($slug) . ' 清空失败');
        }
    }
}
