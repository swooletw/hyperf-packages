<?php

declare(strict_types=1);

use SwooleTW\Hyperf\Support\Facades\Route;

Route::get('/foo', function () {
    return 'foo';
});
