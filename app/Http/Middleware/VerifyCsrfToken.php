<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/service/pay/ali_notify',
        '/service/pay/wx_notify',
        '/service/upload/images',
        '/service/validate_phone/send'
    ];
}
