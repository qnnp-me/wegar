<?php
/*
 * This file is part of webman-auto-route.
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    qnnp<qnnp@qnnp.me>
 * @copyright qnnp<qnnp@qnnp.me>
 * @link      https://qnnp.me
 * @license   https://opensource.org/licenses/MIT MIT License
 */

namespace qnnp\wegar\Module;

use plugin\admin\api\Menu;
use qnnp\wegar\Attribute\BasePath;
use qnnp\wegar\Attribute\Middleware;
use qnnp\wegar\Attribute\RemoveFromDoc;
use qnnp\wegar\Attribute\Route as RouteAttribute;
use qnnp\wegar\Controller\WegarController;
use ReflectionClass;
use ReflectionException;


class Wegar
{

    /**
     * <h2 style="color:#E97230;">扫描注解路由</h2>
     * <span style="color:#E97230;">/app 默认自动加载</span>
     *
     * @param array $apps <span style="color:#E97230;">需要另外加载的 app , 此处 App::class 为 app Controller 目录的一个类</span>
     *                    <pre style="color:#E97230;">[ App::class, ...]</pre>
     *
     * @param bool $init <span style="color:#E97230;">加载默认路由</span>
     */
    static function scan(array $apps = [], bool $init = false): void
    {
        if ($init) {
            $controller_class_list = static::scanControllerClasses('\app', app_path());
            foreach ($controller_class_list as $controller_class => $app_base_namespace) {
                static::scanController($controller_class, $app_base_namespace);
            }
            static::scanAppController(WegarController::class);
            self::checkMenu();
        }

        foreach ($apps as $app) {
            if (class_exists($app)) {
                static::scanAppController($app);
            }
        }
    }

    private static function scanControllerClasses(string $app_base_namespace, string $app_base_path): array
    {
        $controller_class_list = [];
        $app_base_path = realpath($app_base_path);
        $controller_files = static::scanControllerFiles($app_base_path);
        foreach ($controller_files as $controller_file) {
            $controller_class = preg_replace("/\.php$/i", '', $controller_file);
            $controller_class = str_replace($app_base_path, $app_base_namespace, $controller_class);
            $controller_class = str_replace("/", '\\', $controller_class);
            if (class_exists($controller_class)) {
                $controller_class_list[$controller_class] = $app_base_namespace;
            }
        }
        return $controller_class_list;
    }

    private static function scanControllerFiles(string $controller_dir_path): array
    {
        $controller_files = [];
        if (is_dir($controller_dir_path)) {
            $dir_items = scandir($controller_dir_path);
            foreach ($dir_items as $item) {
                $item_realpath = $controller_dir_path . DIRECTORY_SEPARATOR . $item;
                if (!preg_match("/^\..*/", $item) && is_dir($item_realpath)) {
                    array_push($controller_files, ...static::scanControllerFiles($item_realpath));
                } elseif (preg_match("/[\/\\\]controller/i", $item_realpath) && preg_match("/\.php$/i", $item)) {
                    $controller_files[] = $item_realpath;
                }
            }
        }
        return $controller_files;
    }

    /**
     * @throws ReflectionException
     */
    private static function scanController(string $controller_class, string $app_base_namespace): void
    {
        $prefix = '';
        /** 给定类的反射类 */
        $controller_class_ref = new ReflectionClass($controller_class);
        if (!!($base_path = $controller_class_ref->getAttributes(BasePath::class))) {
            $prefix = $base_path[0]->newInstance()->path;
        }
        /** Controller 组中间件 */
        $controller_middleware = $controller_class_ref->getAttributes(Middleware::class);
        $controller_middleware = count($controller_middleware) > 0 ? $controller_middleware[0]->newInstance()->middleware : [];
        /** 获取类里的所有方法 */
        $controller_endpoints = $controller_class_ref->getMethods();
        /** 遍历类方法 */
        foreach ($controller_endpoints as $controller_endpoint) {
            $endpoints = $controller_endpoint->getAttributes(RouteAttribute::class);
            foreach ($endpoints as $endpoint) {
                /**
                 * 路由对象
                 *
                 * @var RouteAttribute $endpoint_route
                 */
                $endpoint_route = $endpoint->newInstance();
                $endpoint_route->controllerClassRef = $controller_class_ref;
                /** 设置的路由对象的参数列表 */
                $arguments = $endpoint->getArguments();
                // 处理 ./ 相对路径开头
                $path = preg_replace("/^\.\//", '', $arguments[0] ?? $arguments['route'] ?? '');
                if ($path === '') {
                    $path = $controller_endpoint->name === 'index' ? '' : $controller_endpoint->name;
                }
                // 路由对应方法，用于添加路由
                $endpoint_method = $controller_class_ref->name . '@' . $controller_endpoint->name;
                /** 相对路径子路由处理 */
                if (!preg_match("/^[\/\\\]/", $path)) {
                    // 驼峰转换
                    $base_path = strtolower(preg_replace("/([a-z0-9])([A-Z])/", "$1-$2", $controller_class_ref->name));
                    $base_namespace = strtolower(preg_replace("/([a-z0-9])([A-Z])/", "$1-$2", $app_base_namespace));
                    // 反斜杠处理
                    $base_path = str_replace('\\', '/', $base_path);
                    $base_namespace = str_replace('\\', '/', $base_namespace);
                    $base_namespace = preg_replace('/^\//', '', $base_namespace); // 去除用户可能携带的开头斜杠
                    // 去除基本命名空间开头
                    $base_path = str_replace($base_namespace, '', $base_path);
                    // 路径中移除 controller 目录
                    $base_path = str_replace('/controller/', '/', $base_path);
                    // 拼接实际路径
                    $path = $base_path . (empty($path) ? '' : '/' . $path);
                }
                // 驼峰转换
                $path = strtolower(preg_replace("/([a-z0-9])([A-Z])/", "$1-$2", $path));
                // 移除 controller_suffix
                $controller_suffix = strtolower(config('app.controller_suffix', 'controller'));
                $path = preg_replace("/-$controller_suffix/", '', $path);
                /** 添加路由 */
                $endpoint_route
                    ->addToRoute($prefix . $path, $endpoint_method)
                    ->name($controller_class_ref->getShortName())
                    ->middleware($endpoint_route->getMiddleware($controller_middleware));
                if (
                    !$controller_class_ref->getAttributes(RemoveFromDoc::class)
                    && !$controller_endpoint->getAttributes(RemoveFromDoc::class)
                ) {
                    /** 添加到 OpenAPI 文档 */
                    $endpoint_route->addToDoc($controller_endpoint);
                }
            }
        }
    }

    /**
     * @throws ReflectionException
     */
    private static function scanAppController($class_in_root): void
    {
        $class_ref = new ReflectionClass($class_in_root);

        $app_base_namespace = $class_ref->getNamespaceName();
        $app_base_path = dirname($class_ref->getFileName());

        $controller_class_list = self::scanControllerClasses($app_base_namespace, $app_base_path);

        foreach ($controller_class_list as $controller_class => $app_base_namespace) {
            static::scanController($controller_class, $app_base_namespace);
        }
    }

    private static function checkMenu(): void
    {
        if (!class_exists('\plugin\admin\api\Menu::class')) return;
        $lock_file = fopen(runtime_path('wegar-menu.lock'), 'a+');
        if (flock($lock_file, LOCK_EX)) {
            try {
                $dev_menu = Menu::get('dev');
                if (!Menu::get(WegarController::class) && $dev_menu) {
                    $pid = $dev_menu['id'];
                    Menu::add([
                        'title' => 'Wegar Doc',
                        'href' => '/wegar/swagger',
                        'pid' => $pid,
                        'key' => WegarController::class,
                        'weight' => 0,
                        'type' => 1,
                    ]);
                    print "✅ 创建 Wegar 管理菜单\n";
                }
            } catch (\Exception $exception) {
                print "❌ 创建 Wegar 管理菜单\n";
                print $exception->getMessage() . PHP_EOL;
                print $exception->getTraceAsString() . PHP_EOL;
            }
        }
    }

    public static function config($key, $default = null)
    {
        return config("plugin.qnnp.wegar.wegar.$key", $default);
    }
}
