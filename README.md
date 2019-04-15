# cache-response
[![Latest Stable Version](https://poser.pugx.org/fts/cache-response/v/stable)](https://packagist.org/packages/fts/cache-response)
[![Total Downloads](https://poser.pugx.org/fts/cache-response/downloads)](https://packagist.org/packages/fts/cache-response)
[![License](https://poser.pugx.org/fts/cache-response/license)](https://packagist.org/packages/fts/cache-response)

# 功能
* 生成静态化页面
# 安装
    composer require fts/cache-response
### 添加服务提供者
打开 `config/app.php` 并添加以下内容到 providers 数组:
    
    fts\CacheResponse\CacheResponseServiceProvider.php::class
### 中间件
打开 `app/Http/Kernel.php` 并添加以下内容到 routeMiddleware 数组:

    'page-cache' => fts\CacheResponse\Middleware\CacheResponse::class
### URL重写
* 对于nginx:
    
    更新`location`块`try_files`内容
    
        location = / {
            try_files /static/index.html /index.php?$query_string;
        }
        
        location / {
            try_files $uri $uri/ /static/$uri.html /index.php?$query_string;
        }
    
* 对于apache:

    打开`public\.htaccess` 添加以下内容
    
        RewriteCond %{REQUEST_URI} ^/?$
        RewriteCond %{DOCUMENT_ROOT}/static/index.html -f
        RewriteRule .? page-cache/index.html [L]
        RewriteCond %{DOCUMENT_ROOT}/static%{REQUEST_URI}.html -f
        RewriteRule . static%{REQUEST_URI}.html [L]
# 用法
### 使用中间件
    Router::middleware('page-cache')->get('');
### 清除缓存
`uri`:可选参数，指定要删除缓存的静态文件

    php artisan page-cache:clear uri
### 生成缓存
`uri`:可选参数，指定要生成缓存的url

    php artisan page-cache:create uri
# 配置参数
    config/pageCache.php
    
* 是否自动失效:
    
        isAutoExpired：true | false
    
* 有效时间（分钟）:
    
        expire：默认30分钟
    
* 缓存目录:

        cachePath：path