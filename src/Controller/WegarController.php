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

namespace qnnp\wegar\Controller;

use qnnp\wegar\Attribute\Middleware;
use qnnp\wegar\Attribute\RemoveFromDoc;
use qnnp\wegar\Attribute\Route;
use qnnp\wegar\Module\OpenAPI;
use qnnp\wegar\Module\WegarAuthMiddleware;
use support\Request;
use support\Response;

#[RemoveFromDoc]
#[Middleware([WegarAuthMiddleware::class])]
class WegarController
{
	#[Route('{all:(?!openapi\.json).*}', methods: ['get', 'post'])]
	public function swagger(
		Request $request,
		string  $path = '',
	): Response
	{
		$path = $path !== 'swagger' ? $path : 'index.html';
		$custom_file = realpath(dirname(__DIR__) . '/public/swagger/' . $path);
		$swagger_file = realpath(base_path() . '/vendor/swagger-api/swagger-ui/dist/' . $path);
		if (is_file($custom_file) || is_file("phar://webman.phar/" . $custom_file)) {
			return response('')->file($custom_file);
		} elseif (is_file($swagger_file)) {
			return response('')->file($swagger_file);
		}
		return response('<h1>404</h1>')->withStatus(404);
	}

	#[Route('openapi.json',)]
	public function openapi(): Response
	{
		return json(OpenAPI::generate(), JSON_PRETTY_PRINT);
	}
}
