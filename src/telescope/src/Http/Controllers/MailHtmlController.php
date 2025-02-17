<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Telescope\Http\Controllers;

use SwooleTW\Hyperf\Telescope\Contracts\EntriesRepository;

class MailHtmlController
{
    /**
     * Get the HTML content of the given email.
     */
    public function show(EntriesRepository $storage, string $id): mixed
    {
        return $storage->find($id)->content['html'];
    }
}
