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

class OpenAPI {
  /**
   * @var string $openapi <span style="color:#E97230;">OpenAPI 版本，尽量不改，默认：3.0.3</span>
   */
  protected static string $openapi      = '3.0.3';
  protected static array  $info         = [];
  protected static array  $paths        = [];
  protected static array  $tags         = [];
  protected static array  $components   = [];
  protected static array  $security     = [];
  protected static array  $servers      = [];
  protected static array  $externalDocs = [];
  protected static array  $extend       = [];

  static function toJson(): bool|string {
    return json_encode(static::toArray());
  }

  static function toArray(): array { return static::generate(); }

  static function generate(): array {
    $info = array_replace_recursive(
      [
        'title'          => config('server.name','项目标题'),
        'description'    => '项目描述',
        'version'        => '0.0.0',
        'termsOfService' => 'http://localhost/service.html',
        'contact'        => [
          'name'  => '联系人',
          'url'   => 'http://localhost/contact.html',
          'email' => 'example@example.com'
        ],
        'license'        => [
          'name' => 'API许可',
          'url'  => 'http://localhost/license.html'
        ],
      ],
      static::$info
    );
    $doc  = [
      'openapi' => static::$openapi,
      'info'    => $info,
    ];
    count(static::$tags) > 0 && $doc['tags'] = static::$tags;
    count(static::$servers) > 0 && $doc['servers'] = static::$servers;
    count(static::$security) > 0 && $doc['security'] = static::$security;
    count(static::$components) > 0 && $doc['components'] = static::$components;
    count(static::$externalDocs) > 0 && $doc['externalDocs'] = static::$externalDocs;
    $doc          = array_replace_recursive($doc, static::$extend);
    $doc['paths'] = static::$paths;

    return $doc;
  }

  static function addPath(array $paths): void {
    foreach ($paths as $path => $method_config) {
      !isset(static::$paths[$path]) && static::$paths[$path] = [];
      foreach ($method_config as $method => $_values) {
        static::$paths[$path][$method] = $_values;
      }
    }
  }

  static function addTag(array $tag) {
    foreach (static::$tags as $_tag) {
      if ($_tag['name'] == $tag['name']) return null;
    }
    static::$tags[] = $tag;
  }

  static function setOpenAPIVersion(?string $version): void {
      $version ?? static::$openapi = $version;
  }

  static function setInfo(array $info): void {
    static::$info = array_replace_recursive(
      ['version' => config('app.version') ?: '0.0.0-dev'],
      static::$info,
      $info
    );
  }

  static function setSecurity(array $security): void {
    static::$security = array_replace_recursive(static::$security, $security);
  }

  static function setTags(array $tags): void {
    static::$tags = array_replace_recursive(static::$tags, $tags);
  }

  static function setExternalDocs(array $externalDocs): void {
    static::$externalDocs = array_replace_recursive(static::$externalDocs, $externalDocs);
  }

  public static function setExtend(array $extend): void {
    static::$extend = array_replace_recursive(static::$extend, $extend);
  }

  static function setServers(array $servers): void {
    static::$servers = array_replace_recursive(static::$servers, $servers);
  }

  static function setSecuritySchemes(array $securitySchemes): void {
    count($securitySchemes) > 0 && static::$components = array_replace_recursive(
      static::$components, [
                           'securitySchemes' => $securitySchemes
                         ]
    );
  }

  public static function getComponents(): array {
    return static::$components;
  }

  static function setComponents(array $components): void {
    static::$components = array_replace_recursive(static::$components, $components);
  }
}
