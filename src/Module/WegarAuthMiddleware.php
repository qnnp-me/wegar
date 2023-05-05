<?php

namespace qnnp\wegar\Module;

use plugin\admin\app\middleware\AccessControl;
use ReflectionException;
use support\exception\BusinessException;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class WegarAuthMiddleware implements MiddlewareInterface
{

	/**
	 * @inheritDoc
	 * @throws BusinessException|ReflectionException
	 */
	public function process(Request $request, callable $handler): Response
	{
		$headers = [
			'Cache-Control' => 'no-cache'
		];
		if (class_exists(AccessControl::class)) {
			return (new AccessControl())->process($request, $handler)->withHeaders($headers);
		} elseif ($conf_passwd = Wegar::config('password', '')) {
			$passwd = $request->post('wegar-auth');
			if ($passwd === $conf_passwd) {
				$request->session()->set('wegar-auth', md5($passwd));
				return response("<script>navigation.back()</script>")->withHeaders($headers);
			}
			if (session('wegar-auth') === md5($conf_passwd)) {
				return $handler($request)->withHeaders($headers);
			} else {
				return response('')->file(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public/swagger/auth.html')->withHeaders($headers);
			}
		} else {
			return $handler($request)->withHeaders($headers);
		}
	}
}
