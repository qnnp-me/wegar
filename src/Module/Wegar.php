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

use qnnp\wegar\Attribute\Route as RouteAttribute;
use ReflectionClass;
use ReflectionException;


class Wegar
{
    /**
     * @var bool $withOpenapiDoc <span style="color:#E97230;">是否生成 OpenAPI 文档</span>
     */
    protected static bool $withOpenapiDoc = true;
    protected static bool $appLoaded = false;
    protected static bool $openapiLoaded = false;

    /**
     * <h2 style="color:#E97230;">加载注解路由</h2>
     * <span style="color:#E97230;">/app 默认自动加载</span>
     *
     * @param array $apps <span style="color:#E97230;">需要另外加载的目录</span>
     *                    <pre style="color:#E97230;">[ [命名空间根(/app), 目录绝对路径(app_path())], ...]</pre>
     *
     * @param bool $with_openapi_doc <span style="color:#E97230;">OpenAPI 文档开关（默认：true）</span>
     *
     * @throws ReflectionException
     */
    static function load(array $apps = [], bool $with_openapi_doc = true): void
    {
        static::$withOpenapiDoc = $with_openapi_doc;
        $controller_class_list = [];
        // TODO 多次加载？
        if (!self::$appLoaded) {
            self::$appLoaded = true;
            static::scanControllerClasses('\app', app_path(), $controller_class_list);
        }
        if (!self::$openapiLoaded && $with_openapi_doc) {
            self::$openapiLoaded = true;
            static::scanControllerClasses(
                'qnnp\wegar\Controller',
                dirname(__DIR__) . '/Controller',
                $controller_class_list
            );
        }
        foreach ($apps as $app) {
            static::scanControllerClasses($app[0], $app[1], $controller_class_list);
        }
        foreach ($controller_class_list as $class => $namespace) {
            static::scanController($class, $namespace);
        }
    }

    protected static function scanControllerClasses(string $app_namespace, string $app_dir_path, array &$controller_class_list): void
    {
        $app_dir_path = realpath($app_dir_path);
        $controller_files = static::scanControllerFiles($app_dir_path);
        foreach ($controller_files as $controller_file) {
            $controller_class = preg_replace("/\.php$/i", '', $controller_file);
            $controller_class = str_replace($app_dir_path, $app_namespace, $controller_class);
            $controller_class = str_replace("/", '\\', $controller_class);
            if (class_exists($controller_class)) {
                $controller_class_list[$controller_class] = $app_namespace;
            }
        }
    }

    protected static function scanControllerFiles(string $controller_dir_path): array
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
    protected static function scanController(string $controller_class, string $namespace): void
    {
        /** 给定类的反射类 */
        $controller_class_ref = new ReflectionClass($controller_class);
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
                    $base_namespace = strtolower(preg_replace("/([a-z0-9])([A-Z])/", "$1-$2", $namespace));
                    // 反斜杠处理
                    $base_path = str_replace('\\', '/', $base_path);
                    $base_namespace = str_replace('\\', '/', $base_namespace);
                    $base_namespace = preg_replace('/^\//', '', $base_namespace); // 去除用户可能携带的开头斜杠
                    // 去除基本命名空间开头
                    $base_path = str_replace($base_namespace, '', $base_path);
                    // 路径中移除 controller 目录
                    $base_path = str_replace('/controller', '', $base_path);
                    // 路径中移除 index 类名
                    $base_path = str_replace('/index', '', $base_path);
                    // 拼接实际路径
                    $path = $base_path . (empty($path) ? '' : '/' . $path);
                }
                // 驼峰转换
                $path = strtolower(preg_replace("/([a-z0-9])([A-Z])/", "$1-$2", $path));
                // 移除 controller_suffix
                $controller_suffix = strtolower(config('app.controller_suffix', 'controller'));
                $path = preg_replace("/-$controller_suffix/", '', $path);
                /** 添加路由 */
                $endpoint_route->add($path, $endpoint_method);
                /** 添加到 OpenAPI 文档 */
                static::$withOpenapiDoc && $endpoint_route->addToOpenAPIDoc($controller_endpoint);
            }
        }
    }
}
