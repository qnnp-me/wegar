# qnnp/wegar
webman 自动注解路由插件，自动生成 OpenAPI 3.0 规范文档，支持webman-admin权限管理

## 示例

```php
<?php
# file:/app/api/controller/index.php

namespace app\api\controller

use qnnp\wegar\Attributes\Route;
use support\Request;
use support\Response;

class Index {
  #[Route(
    './',                               // 路由 /api/test
    middleware: [RequireLogin::class],  // 加载中间件
    get: [                              // GET 方法各项参数
      'orderBy' => [
        get::schema => [
          schema::enum => ['status', 'last_active_time', 'created_at']
        ]
      ],
      'order'   => [
        get::schema  => [
          schema::enum => ['asc', 'desc']
        ],
        get::example => 'desc',
      ],
      'page'    => [
        get::example => 1,
        get::schema  => ['type' => 'integer']
      ],
      'limit'   => [
        get::example => 10,
        get::schema  => [
          schema::type => 'integer'
        ]
      ],
    ],
    tags: ['测试分组'],                  // 路由分组
    summary: '测试路由"/api/test"',      // 路由描述
  )]
  public function test(Request $request):Response {
    return json('success');
  }
}
```

### SwaggerUI 显示效果
![示例截图](https://qnnp.me/wp-content/uploads/2022/10/example-test-get.png "示例截图")

### OpenAPI 结果
```json
{
  ...,
  
  "paths": {
    "/api/test": {
      "get": {
        "summary": "测试路由\"/api/test\"",
        "description": "",
        "responses": {
          "default": {
            "description": ""
          }
        },
        "tags": [
          "测试分组"
        ],
        "parameters": [
          {
            "in": "query",
            "schema": {
              "type": "string",
              "enum": [
                "status",
                "last_active_time",
                "created_at"
              ]
            },
            "name": "orderBy"
          },
          {
            "in": "query",
            "schema": {
              "type": "string",
              "enum": [
                "asc",
                "desc"
              ]
            },
            "example": "desc",
            "name": "order"
          },
          {
            "in": "query",
            "schema": {
              "type": "integer"
            },
            "example": 1,
            "name": "page"
          },
          {
            "in": "query",
            "schema": {
              "type": "integer"
            },
            "example": 10,
            "name": "limit"
          }
        ]
      }
    }
  }
}
```
