<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    // для каких адресов не нужно использовать csrf токен
    protected $except = [
        'payment/*',
    ];
}
