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
		if (class_exists(AccessControl::class)) {
			return (new AccessControl())->process($request, $handler);
		} elseif ($conf_passwd=Wegar::config('password','')) {
			$passwd = $request->post('wegar-auth');
			if ($passwd===$conf_passwd) {
				$request->session()->set('wegar-auth', md5($passwd));
				return response("<script>navigation.back()</script>"
				);
			}
			if (session('wegar-auth') === md5($conf_passwd)) {
				return $handler($request);
			} else {
				return response('')->file(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public/swagger/auth.html');
			}
		} else {
			return $handler($request);
		}
	}
}
