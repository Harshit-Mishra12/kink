<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/v1/fetch-questions',
        'api/v1/create-user',
        'api/v1/user/save-responses',
        'api/v1/fetch-contactus',
        'api/v1/fetch-aboutus',
        'api/v1/fetch-privacypolicy',
        'api/v1/fetch-imprint',
        'api/v1/fetch-results',
        'api/v1/download-report',
        'api/v1/fetch-categorybyid'

    ];
}
