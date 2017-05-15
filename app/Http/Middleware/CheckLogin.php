<?php

namespace App\Http\Middleware;
use App\Models\ErrorCode;
use App\Tool\Validate\Aes;
use Closure;

class CheckLogin
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
        $login_token = $request->get('token', '');
        $uid = $request->get('uid', '');
        if ($login_token == '' || $uid == '') {
            $errArr = ErrorCode::$UNAUTHORIZED;
            return response()->json([
                'errorCode'  => $errArr[0],
                'errorStr'   => $errArr[1],
                'resultCount'=> 0,
                'results'    => null,
                'extraInfo'  => null
            ]);
        }
        $decrypt = Aes::letSecret($login_token, 'D', config('app.login_token'));
        if ($uid != $decrypt) {
          $return_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
          $errArr = ErrorCode::$UNAUTHORIZED;
          return response()->json([
              'errorCode'  => $errArr[0],
              'errorStr'   => $errArr[1],
              'resultCount'=> 0,
              'results'    => null,
              'extraInfo'  => urlencode($return_url)
          ]);
        }
        return $next($request);
    }
}
