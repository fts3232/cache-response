<?php

namespace fts\CacheResponse;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request as LaravelRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 页面缓存类
 *
 * Class Cache
 * @package fts\CacheResponse
 */
class Cache
{
    /**
     * 文件系统类
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * 缓存目录
     *
     * @var string
     */
    protected $cachePath;

    /**
     * Cache constructor.
     * @param Filesystem $files 文件系统类
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * 设置缓存目录
     *
     * @param $path
     */
    public function setCachePath($path)
    {
        $this->cachePath = rtrim($path, '\/');
    }

    /**
     * 生成cache
     *
     * @param Request  $request  请求类
     * @param Response $response 响应类
     */
    public function cache(Request $request, Response $response)
    {
        //获取请求对应的文件名和缓存目录
        list($path, $file) = $this->getDirectoryAndFileNames($request);
        //创建目录
        $this->files->makeDirectory($path, 0775, true, true);
        //写入文件
        $this->files->put(
            $this->join([$path, $file]),
            $response->getContent(),
            true
        );
    }

    /**
     * 获取缓存目录
     *
     * @return string
     */
    public function getCachePath()
    {
        $base = $this->cachePath ? $this->cachePath : public_path('static');
        if (is_null($base)) {
            throw new Exception('Cache path not set.');
        }
        return $this->join(array_merge([$base], func_get_args()));
    }

    /**
     * 获取请求对应的文件名和缓存目录
     *
     * @param $request 请求类
     * @return array
     */
    protected function getDirectoryAndFileNames($request)
    {
        $segments = explode('/', ltrim($request->getPathInfo(), '/'));
        $fileName = array_pop($segments);
        $fileName = $fileName ?: 'index';
        $file = $fileName . '.html';
        return [$this->getCachePath(implode('/', $segments)), $file];
    }

    /**
     * 路径拼接
     *
     * @param array $paths 路径数组
     * @return string
     */
    protected function join(array $paths)
    {
        //过滤左右2边的/
        $trimmed = array_map(function ($path) {
            return trim($path, '/');
        }, $paths);
        //判断是否需要前面拼接/
        return $this->matchRelativity(
            $paths[0],
            implode('/', array_filter($trimmed))
        );
    }

    /**
     * 判断是否需要前面拼接/
     *
     * @param string $source 源路径
     * @param string $target 目标路径
     * @return string
     */
    protected function matchRelativity($source, $target)
    {
        return $source[0] == '/' ? '/' . $target : $target;
    }

    /**
     * 判断缓存文件是否存在
     *
     * @param Request $request 请求类
     * @return bool|string
     */
    public function exists(Request $request)
    {
        list($path, $file) = $this->getDirectoryAndFileNames($request);
        $fileName = $this->join([$path, $file]);

        if ($this->files->exists($fileName)) {
            return $this->files->get($fileName);
        }
        return false;
    }

    /**
     * 删除特定的缓存文件
     *
     * @param string $slug 指定的缓存文件名
     * @return bool
     */
    public function forget($slug)
    {
        return $this->files->delete($this->getCachePath($slug . '.html'));
    }

    /**
     * 清理缓存目录
     *
     * @param string $directory 指定的目录名
     * @return bool
     */
    public function clear($directory = '')
    {
        //如果没指定目录，清理缓存目录所有内容
        if (!empty($slug)) {
            return $this->files->deleteDirectory($this->getCachePath($directory));
        } else {
            return $this->files->cleanDirectory($this->getCachePath());
        }
    }

    /**
     * 根据uri生成缓存文件
     *
     * @param string $uri 要生成缓存文件的uri
     * @return bool
     */
    public function createCache($uri)
    {
        //路由处理
        $kernel = app(Kernel::class);
        $request = LaravelRequest::createFromBase(Request::create($uri));
        $response = $kernel->handle($request);
        //是否生成缓存
        return $this->shouldCache($request, $response);
    }

    /**
     * 判断是否需要缓存
     *
     * @param Request  $request  请求类
     * @param Response $response 响应类
     * @return bool
     */
    protected function shouldCache(Request $request, Response $response)
    {
        //如果请求类型是get并且http code为200 生成缓存
        return $request->isMethod('GET') && $response->getStatusCode() == 200;
    }
}
