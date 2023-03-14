<?php

namespace qnnp\wegar\Module;

use support\Response;

class Res
{
    static function json(
        mixed      $data = null,
        string     $message = '',
        string|int $error_code = 0,
        mixed      $trace = ''
    ): Response
    {
        if (Wegar::config('response.direct', false)) return json($data);

        $response_data = [Wegar::config('response.template.data') => $data];

        if ($message or Wegar::config('response.placeholder', false))
            $response_data[Wegar::config('response.template.message')] = $message;

        if ($error_code or Wegar::config('response.placeholder', false))
            $response_data[Wegar::config('response.template.error_code')] = $error_code;

        if (($error_code or Wegar::config('response.placeholder', false)) && config('app.debug', false))
            $response_data[Wegar::config('response.template.trace')] = $trace;

        return json($response_data);
    }
}
