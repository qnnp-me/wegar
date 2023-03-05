<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

use qnnp\wegar\Module\Wegar;
use support\exception\BusinessException;
use Webman\Route;

/**
 * 加载注解路由
 */
Wegar::scan();
/**
 * 因为本注解路由可通过注解加载中间件
 * 为防止注解的中间件不被加载就被请求
 * 安全起见关闭默认路由功能
 */
Route::disableDefaultRoute();

