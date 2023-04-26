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

namespace qnnp\wegar\Attribute;

use Attribute;
use FastRoute\RouteParser\Std;
use qnnp\wegar\Attribute\Helper\OpenAPI\media;
use qnnp\wegar\Attribute\Helper\OpenAPI\parameter;
use qnnp\wegar\Attribute\Helper\OpenAPI\requestBody;
use qnnp\wegar\Attribute\Helper\OpenAPI\schema;
use qnnp\wegar\Module\OpenAPI;
use ReflectionClass;
use ReflectionMethod;
use Webman\{Route as RouteClass, Route\Route as RouteObject};

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class Route
{
    public string $path = '';
    public ReflectionClass $controllerClassRef;

    /**
     * <h2 style="color:#E97230;">注解路由</h2>
     * <a href="https://swagger.io/specification/#operation-object">OpenAPI 规范文档</a>
     *
     * @param string $route <span style="color:#E97230;">路由 Path</span>
     *
     * @param string|array $methods <span style="color:#E97230;">路由方法 'get'|[get, head, post, put, delete,
     *   connect, options, trace] </span>
     *
     * @param array $middleware <span style="color:#E97230;">路由中间件</span>
     * <a href="https://www.workerman.net/doc/webman#/middleware" style="color:#5A9BF6;">Webman 中间件介绍</a>
     * <pre style="color:#3982F7;">[ MiddleWare::class, ... ]</pre>
     * <hr/>
     *
     * @param array $get <span style="color:#E97230;">get 参数 [parameter]</span>
     * <a href="https://swagger.io/specification/#parameter-object" style="color:#5A9BF6;">规范文档</a>
     * <pre style="color:#3982F7;">[
     *    'field1',
     *    'field2' => [
     *        'type'     => 'boolean',
     *        'required' => true,
     *        'schema'   => [...]
     *    ],
     *    ...
     *]</pre>
     *
     * @param array $post <span style="color:#E97230;">post 参数</span>
     * <a href="https://swagger.io/specification/#schema-object" style="color:#5A9BF6;">规范文档</a>
     * <pre style="color:#3982F7;">[
     *    'field1',
     *    'field2' => [
     *        'type'     => 'boolean',
     *        'required' => true,
     *        'schema'   => [...]
     *    ],
     *    ...
     *]</pre>
     *
     * @param array $file <span style="color:#E97230;">上传文件参数，将会附加到 post 参数列表</span>
     * <pre style="color:#3982F7;">['field1', 'field2' => [...],]</pre>
     *
     * @param array $json <span style="color:#E97230;">json 参数</span>
     * <a href="https://swagger.io/specification/#schema-object" style="color:#5A9BF6;">规范文档</a>
     * <pre style="color:#3982F7;">[
     *    'field1',
     *    'field2' => [
     *        'type'     => 'boolean',
     *        'required' => true,
     *        'schema'   => [...]
     *    ],
     *    ...
     *]</pre>
     *
     * @param array $cookie <span style="color:#E97230;">cookie 参数列表</span>
     * <a href="https://swagger.io/specification/#parameter-object" style="color:#5A9BF6;">规范文档</a>
     * <pre style="color:#3982F7;">['field1', 'field2' => [...], ...]</pre>
     *
     * @param array $header <span style="color:#E97230;">header 参数</span>
     * <a href="https://swagger.io/specification/#parameter-object" style="color:#5A9BF6;">规范文档</a>
     * <pre style="color:#3982F7;">['field1', 'field2' => [...], ...]</pre>
     *
     * @param array $xml <span style="color:#E97230;">xml 参数，参数列表第一个 item 将作为 root 标签名、</span>
     * <a href="https://swagger.io/specification/#schema-object" style="color:#5A9BF6;">规范文档</a>
     * <pre style="color:#3982F7;">[
     *    'root'   => 'tagName',
     *    'field1',
     *    'field2' => [
     *        'type'     => 'boolean',
     *        'required' => true,
     *        'schema'   => [...]
     *    ],
     *    ...
     *]</pre>
     * <hr/>
     *
     * @param bool $requireBody <span style="color:#E97230;">requestBody 数据是否必须</span>
     *
     * @param array $tags <span style="color:#E97230;">[Operation] 方法所属分组</span>
     * <div style="color:#E97230;">直接给 string 就可以，如果需要添加描述等信息只需要注解一次就会自动注册到全局。</div>
     * <pre style="color:#3982F7;">[
     *    '标签名称',
     *    [
     *        'name'            => '标签名称带描述'
     *        'description'     => '标签描述',
     *        'externalDocs'    => [
     *            'description' => '外部文档描述',
     *            'url'         => '外部文档链接',
     *        ]
     *     ]
     *]</pre>
     *
     * @param string $summary <span style="color:#E97230;">[Operation] 方法简介</span>
     * <a href="https://swagger.io/specification/#operation-object" style="color:#5A9BF6;">规范文档</a>
     *
     * @param string $description <span style="color:#E97230;">[Operation] 方法详细说明</span>
     * <a href="https://swagger.io/specification/#operation-object" style="color:#5A9BF6;">规范文档</a>
     *
     * @param array $externalDocs <span style="color:#E97230;">[Operation] 方法外部文档</span>
     *<pre style="color:#3982F7;">[
     *    'description' => '文档描述',
     *    'url'         => '文档链接'
     *]</pre>
     *
     * @param array $parameters <span style="color:#E97230;">[Operation] 接受的参数列表</span>
     * <a href="https://swagger.io/specification/#parameter-object" style="color:#5A9BF6;">规范文档</a>
     * <pre style="color:#3982F7;">[
     *    [
     *        'name'        => '参数名称',
     *        'in'          => 'query', // query|path|header|cookie
     *        'description' => '参数描述说明',
     *        'required'    => true,
     *        'deprecated'  => false,
     *    ],
     *    ...
     *]</pre>
     *
     * @param array $requestBody $requestBody <span style="color:#E97230;">[Operation] requestBody
     *     参数，上方四个快速设置参数满足不了的需求可以设置原生结构</span>
     * <a href="https://swagger.io/specification/#request-body-object" style="color:#5A9BF6;">标准文档</a>
     *
     * @param array $responses <span style="color:#E97230;">[Operation] 返回数据示例</span>
     * <a href="https://swagger.io/specification/#responses-object" style="color:#5A9BF6;">规范文档</a>
     * <pre style="color:#3982F7;">[
     *    200 => [
     *        'description' => 'Success',
     *        'headers'     => [
     *            'x-header' => [
     *                'description' => '描述',
     *                'schema' => [
     *                    'type' => 'integer',
     *                    ...
     *                ],
     *            ]
     *        ],
     *    ]
     *]</pre>
     *
     * @param array $callbacks <span style="color:#E97230;">[Operation] </span>
     * <a href="https://swagger.io/specification/#callback-object" style="color:#5A9BF6;">规范文档</a>
     *
     * @param bool $deprecated <span style="color:#E97230;">[Operation] 声明此方法是否已被废弃</span>
     *
     * @param array $security <span style="color:#E97230;">[Operation] 安全声明</span>
     * <a href="https://swagger.io/specification/#security-requirement-object" style="color:#5A9BF6;">规范文档</a>
     *
     * @param array $servers <span style="color:#E97230;">[Operation] 服务器列表</span>
     * <a href="https://swagger.io/specification/#server-object" style="color:#5A9BF6;">规范文档</a>
     * <hr/>
     *
     * @param array $extend <span style="color:#E97230;">[Operation] 扩展选项</span>
     * <a href="https://swagger.io/specification/#operation-object" style="color:#5A9BF6;">规范文档</a>
     * <div style="color:#E97230;">用于扩展方法的选项、也可以用于强制替换方法选项</div>
     *
     * @link https://swagger.io/specification/#operation-object Operation 规范
     * @link https://swagger.io/specification/ OpenAPI 标准
     */
    public function __construct(
        private string       $route = '',
        private string|array $methods = 'get',
        private array        $middleware = [],
        private array        $get = [],
        private array        $post = [],
        private array        $file = [],
        private array        $json = [],
        private array        $cookie = [],
        private array        $header = [],
        private array        $xml = [],
        private bool         $requireBody = false,
        private array        $tags = [],
        private string       $summary = '',
        private string       $description = '',
        private array        $externalDocs = [],
        private array        $parameters = [],
        private array        $requestBody = [],
        private array        $responses = [],
        private array        $callbacks = [],
        private bool         $deprecated = false,
        private array        $security = [],
        private array        $servers = [],
        private array        $extend = [],
    )
    {

        // 路由路径预处理
        $this->path = preg_replace("/^\.\//", '', $this->route);

        // 路由请求方法
        if (!is_array($this->methods)) $this->methods = [$this->methods];
        $this->methods = array_map('strtoupper', $this->methods);
        if (in_array('ANY', $this->methods)) {
            $this->methods = ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'TRACE'];
        }

        // 响应值
        $this->responses = array_replace_recursive(
            [
                'default' => [
                    'content' => ['application/json' => []]
                ]
            ],
            $this->responses
        );
    }

    public function addToRoute(string $path, mixed $callback): RouteObject
    {
        $this->path = $path;
        $callback = RouteClass::convertToCallable($this->path, $callback);
        return RouteClass::add(
            $this->methods,
            $this->path,
            $callback
        );
    }

    public function getMiddleware($controller_middleware = []): array
    {
        return array_unique([...$controller_middleware, ...$this->middleware]);
    }

    public function addToDoc(ReflectionMethod $endpoint_ref): void
    {
        $endpoint_source = "***Source:** [";
        $endpoint_source .= $endpoint_ref->getFileName();
        $endpoint_source = str_replace(base_path(DIRECTORY_SEPARATOR), '', $endpoint_source);
        $endpoint_source .= ':' . $endpoint_ref->getStartLine() . '](#) ➤ ';
        $endpoint_source .= $endpoint_ref->class;
        $endpoint_source .= $endpoint_ref->isStatic() ? '::' : '->';
        $endpoint_source .= $endpoint_ref->name . '( ';
        foreach ($endpoint_ref->getParameters() as $index => $parameter) {
            if ($index > 0) $endpoint_source .= ', ';
            $endpoint_source .= $parameter->getType() . ' $' . $parameter->name;
        }
        $endpoint_source .= ' )*';

        $this->description .= strlen($this->description) > 0 ? "\n\n---\n\n" : '';
        $this->description .= $endpoint_source;

        $path = $this->path;
        // 处理路由路径
        $paths = (new Std)->parse($path)[0];
        $path = '';
        $path_params = [];
        foreach ($paths as $folder) {
            if (is_array($folder)) {
                $path_params[$folder[0]] = [$folder[1], 0];
                $folder = "{{$folder[0]}}";
            }
            $path .= "{$folder}";
        }
        /**
         * @var parameter[] $parameters <span style="color:#E97230;">处理合并 parameters header cookie get</span>
         */
        $parameters = [];
        $this->prepareParams($this->cookie, 'cookie', $parameters);
        $this->prepareParams($this->header, 'header', $parameters);
        $this->prepareParams($this->get, 'query', $parameters);
        $this->prepareParams($this->parameters, parameters: $parameters);
        // 处理路径参数
        $path_name_list = [];
        foreach ($path_params as $name => $conf) {
            // 读取出路径参数正则内的注释
            $is_matched = preg_match("/(\(\?#[^)]*([^)]*([^(]*\([^()]*\)[^)]*(?R)*)[^(]*)*[^(]*\))/", $conf[0], $matches);
            // 生成字段描述
            $desc = $is_matched ? preg_replace("/(^\(\?#|\)$)/", '', $matches[1]) : '路径参数';
            $pattern = "^" . ($is_matched ? str_replace($matches[1], '', $conf[0]) : $conf[0]) . "$";
            $path_name_list[] = [
                parameter::name        => $name,
                parameter::in          => 'path',
                parameter::required    => true,
                parameter::description => $desc,
                parameter::schema      => [
                    schema::type    => 'string',
                    schema::pattern => $pattern,
                ],
            ];

        }
        array_unshift($parameters, ...$path_name_list);

        /** 处理 requestBody */
        $this->prepareBody($this->post);
        $this->prepareBody($this->json, 'json');
        $this->prepareBody($this->xml, 'xml');
        $this->prepareBody($this->file, 'file');


        //读取需要添加到全局 tags 表的 tag
        $tags = [];
        foreach ($this->tags as $tag) {
            if (is_array($tag)) {
                OpenAPI::addTag($tag);
                $tags[] = $tag['name'];
            } else {
                $tags[] = $tag;
            }
        }

        // 生成方法文档数组
        $operation = [
            'summary'     => $this->summary,
            'description' => $this->description,
            'responses'   => $this->responses,
        ];
        $this->deprecated && $operation['deprecated'] = $this->deprecated;
        count($tags) > 0 && $operation['tags'] = $tags;
        count($parameters) > 0 && $operation['parameters'] = $parameters;
        count($this->externalDocs) > 0 && $operation['externalDocs'] = $this->externalDocs;
        count($this->callbacks) > 0 && $operation['callbacks'] = $this->callbacks;
        count($this->servers) > 0 && $operation['servers'] = $this->servers;
        count($this->security) > 0 && $operation['security'] = $this->security;

        if (count($this->requestBody) > 0) {
            $body = $this->requestBody;
            $this->requireBody && $body['required'] = $this->requireBody;
            $operation['requestBody'] = $body;
        }
        $operation = array_replace_recursive($operation, $this->extend);

        foreach ($this->methods as $method) {
            $method = strtolower($method);
            $this->config = ['path' => $path, 'method' => $method, 'operation' => $operation];
            OpenAPI::addPath([$path => [$method => $operation]]);
        }

    }

    private function prepareParams($data, $type = false, &$parameters = [],): void
    {
        foreach ($data as $key => $item) {
            if (is_array($item)) {
                if (!isset($item['name'])) {
                    $item['name'] = $key;
                }
                $type && $item['in'] = $type;
            } else {
                $item = ['name' => $item];
                $type && $item['in'] = $type;
            }
            $default = ['in' => 'query', 'schema' => ['type' => 'string']];
            $item = array_replace_recursive($default, $item);
            $parameters[] = $item;
        }
    }

    private function prepareBody($fields, $type = 'post',)
    {
        if (count($fields) == 0) return null;
        //
        $request_type = match ($type) {
            'file' => 'multipart/form-data',
            'json' => 'application/json',
            'xml' => 'application/xml',
            default => 'application/x-www-form-urlencoded'
        };
        if (count($this->file) > 0) {
            $request_type = 'multipart/form-data';
        }
        $properties = [];
        $required = [];
        $xml_root = 'root';
        if ($type == 'xml') {
            if (isset($fields['root'])) {
                $xml_root = $fields['root'];
                unset($fields['root']);
            } elseif (isset($fields[0]) && $fields[0]) {
                $xml_root = $fields[0];
                unset($fields[0]);
            }
        }
        foreach ($fields as $key => $conf) {
            if (is_array($conf)) {
                if (isset($conf['required'])) {
                    $conf['required'] && $required[] = $key;
                    unset($conf['required']);
                }

                // post
                // 携带文件上传字段的话转成 multipart/form-data
                // 因为 webman 框架的 $request->file() 只 支持这种形式的上传
                if (isset($conf['type']) && $conf['type'] == 'file') { // 规范不支持 type = file
                    $conf['type'] = 'string';
                    $conf['format'] = 'binary';
                }
                if ($type == 'post' && isset($conf['format']) && $conf['format'] == 'binary') {
                    $request_type = 'multipart/form-data';
                }
                if (!isset($conf['type'])) $conf['type'] = 'string';
                $properties[$key] = $conf;
            } else {
                $properties[$conf] = ['type' => 'string'];
                if ($type == 'file') {
                    $properties[$conf]['format'] = 'binary';
                }
            }
        }
        $body = [
            requestBody::content => [
                $request_type => [
                    media::schema => [
                        schema::properties => $properties,
                        schema::type       => 'object'
                    ]
                ]
            ]
        ];
        count($required) > 0 && $body['content'][$request_type][media::schema]['required'] = $required;
        if ($type == 'xml') {
            $body[requestBody::content][$request_type]['schema']['xml'] = ['name' => $xml_root];
        }
        $this->requestBody = array_replace_recursive(
            $body,
            $this->requestBody
        );

    }
}

