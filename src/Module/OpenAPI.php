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

use qnnp\wegar\Attribute\Helper\OpenAPI\contact;
use qnnp\wegar\Attribute\Helper\OpenAPI\info;
use qnnp\wegar\Attribute\Helper\OpenAPI\license;
use qnnp\wegar\Attribute\Helper\OpenAPI\openapi as root;

class OpenAPI
{
    protected static string $openapi = '3.0.3';
    protected static array $info = [];
    protected static array $paths = [];
    protected static array $tags = [];
    protected static array $components = [];
    protected static array $security = [];
    protected static array $servers = [];
    protected static array $externalDocs = [];
    protected static array $extend = [];

    static function toJson(): bool|string
    {
        return json_encode(static::toArray());
    }

    static function toArray(): array
    {
        return static::generate();
    }

    static function generate(): array
    {
        $info = array_replace_recursive(
            [
                info::title => config('wegar.title', 'Wegar'),
                info::description => config('wegar.description', ''),
                info::version => config('wegar.version', '0.0.1-dev'),
                info::termsOfService => config('wegar.termsOfService', ''),
                info::contact => [
                    contact::name => config('wegar.contact.name', ''),
                    contact::url => config('wegar.contact.url', ''),
                    contact::email => config('wegar.contact.email', '')
                ],
                info::license => [
                    license::name => config('wegar.license.name', ''),
                    license::url => config('wegar.license.url', '')
                ],
            ],
            static::$info
        );
        $doc = [
            root::openapi => static::$openapi,
            root::info => $info,
        ];
        static::$tags && $doc['tags'] = static::$tags;
        static::$servers && $doc['servers'] = static::$servers;
        static::$security && $doc['security'] = static::$security;
        static::$components && $doc['components'] = static::$components;
        static::$externalDocs && $doc['externalDocs'] = static::$externalDocs;
        $doc = array_replace_recursive($doc, static::$extend);
        $doc['paths'] = static::$paths;

        return $doc;
    }

    static function addPath(array $paths): void
    {
        foreach ($paths as $path => $method_config) {
            !isset(static::$paths[$path]) && static::$paths[$path] = [];
            foreach ($method_config as $method => $_values) {
                static::$paths[$path][$method] = $_values;
            }
        }
    }

    static function addTag(array $tags)
    {
        foreach (static::$tags as $tag) {
            if ($tag['name'] == $tags['name']) return null;
        }
        static::$tags[] = $tags;
    }

    static function setOpenAPIVersion(?string $version): void
    {
            $version ?? static::$openapi = $version;
    }

    static function setInfo(array $info): void
    {
        static::$info = array_replace_recursive(
            static::$info,
            $info
        );
    }

    static function setSecurity(array $security): void
    {
        static::$security = array_replace_recursive(static::$security, $security);
    }

    static function setTags(array $tags): void
    {
        static::$tags = array_replace_recursive(static::$tags, $tags);
    }

    static function setExternalDocs(array $externalDocs): void
    {
        static::$externalDocs = array_replace_recursive(static::$externalDocs, $externalDocs);
    }

    public static function setExtend(array $extend): void
    {
        static::$extend = array_replace_recursive(static::$extend, $extend);
    }

    static function setServers(array $servers): void
    {
        static::$servers = array_replace_recursive(static::$servers, $servers);
    }

    static function setSecuritySchemes(array $securitySchemes): void
    {
        $securitySchemes && static::$components = array_replace_recursive(
            static::$components, [
                'securitySchemes' => $securitySchemes
            ]
        );
    }

    public static function getComponents(): array
    {
        return static::$components;
    }

    static function setComponents(array $components): void
    {
        static::$components = array_replace_recursive(static::$components, $components);
    }
}
