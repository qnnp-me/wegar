window.onload = function () {
  const HideCurlPlugin = (system) => {
    return {
      wrapComponents: {
        curl: () => () => null
      }
    }
  }

  window.ui = SwaggerUIBundle(
    {
      displayRequestDuration: 60000,
      url:'openapi.json',
      dom_id: '#swagger-ui',
      docExpansion: 'none',
      filter: true,
      tryItOutEnabled: true,
      // 保存验证信息
      persistAuthorization: true,
      // tagsSorter(a, b) {
      //   if (a === 'default') return 1
      //   if (b === 'default') return -1
      //   return a.localeCompare(b, 'zh-hans')
      // },
      requestInterceptor(res) {  // 请求
        return res
      },
      responseInterceptor(res) {  // 响应
        return res
      },
      // 请求代码
      requestSnippetsEnabled: false,
      plugins: [
        // HideCurlPlugin,
        HierarchicalTagsPlugin,
      ],
    })
}
