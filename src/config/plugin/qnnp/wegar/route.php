<?php

use qnnp\wegar\Module\Wegar;
use Webman\Route;

/**
 * 加载注解路由
 */
Wegar::scan(main: true);
/**
 * 因为本注解路由可通过注解加载中间件
 * 为防止注解的中间件不被加载就被请求
 * 安全起见关闭默认路由功能
 */
Route::disableDefaultRoute();

