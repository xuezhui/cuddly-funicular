<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class AllowOrigin
{
    /**
     * Run the request filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        $allow_origins = [
            'http://web.zhiheind.com',
            'http://web.zhiheind.com:8080',
            'http://api.zhiheind.com',
            'http://www.zhiheind.com'
        ];
        if (in_array($origin, $allow_origins)) {
            header('Access-Control-Allow-Origin:'.$origin);
        }
        //header('Access-Control-Allow-Origin: *');
        return $next($request);
    }
}
