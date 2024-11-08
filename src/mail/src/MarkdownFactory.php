<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Mail;

use Hyperf\Contract\ConfigInterface;
use Hyperf\ViewEngine\Contract\FactoryInterface;

class MarkdownFactory
{
    public function __construct(
        protected readonly FactoryInterface $factory,
        protected readonly ConfigInterface $config,
    ) {
    }

    public function __invoke(): Markdown
    {
        return new Markdown(
            $this->factory,
            $this->config->get('mail.markdown', [])
        );
    }
}
