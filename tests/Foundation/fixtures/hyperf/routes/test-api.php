<?php

declare(strict_types=1);

use SwooleTW\Hyperf\Http\Request;
use SwooleTW\Hyperf\Http\Response;
use SwooleTW\Hyperf\Support\Facades\Route;

Route::get('/foo', function () {
    return 'foo';
});

Route::get('/server-params', function (Request $request, Response $response) {
    return $response->json(
        $request->getServerParams()
    );
});
