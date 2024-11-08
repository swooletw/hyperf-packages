<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Mail;

use SwooleTW\Hyperf\Mail\Contracts\Factory as FactoryContract;
use SwooleTW\Hyperf\Mail\Contracts\Mailer as MailerContract;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                FactoryContract::class => MailManager::class,
                MailerContract::class => MailerFactory::class,
                Markdown::class => MarkdownFactory::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The configuration file of mail.',
                    'source' => __DIR__ . '/../publish/mail.php',
                    'destination' => BASE_PATH . '/config/autoload/mail.php',
                ],
            ],
        ];
    }
}
