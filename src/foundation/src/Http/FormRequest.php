<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Foundation\Http;

use Hyperf\Validation\Request\FormRequest as HyperfFormRequest;
use SwooleTW\Hyperf\Auth\Access\AuthorizationException;

class FormRequest extends HyperfFormRequest
{
    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization()
    {
        throw new AuthorizationException();
    }
}
