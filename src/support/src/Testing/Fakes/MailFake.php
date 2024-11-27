<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Support\Testing\Fakes;

use Closure;
use Hyperf\Collection\Arr;
use Hyperf\Collection\Collection;
use Hyperf\Support\Traits\ForwardsCalls;
use PHPUnit\Framework\Assert as PHPUnit;
use SwooleTW\Hyperf\Mail\Contracts\Factory;
use SwooleTW\Hyperf\Mail\Contracts\Mailable;
use SwooleTW\Hyperf\Mail\Contracts\Mailer;
use SwooleTW\Hyperf\Mail\MailManager;
use SwooleTW\Hyperf\Mail\PendingMail;
use SwooleTW\Hyperf\Mail\SentMessage;
use SwooleTW\Hyperf\Support\Traits\ReflectsClosures;

class MailFake implements Factory, Fake, Mailer
{
    use ForwardsCalls;
    use ReflectsClosures;

    /**
     * The mailer currently being used to send a message.
     */
    protected ?string $currentMailer = null;

    /**
     * All of the mailables that have been sent.
     */
    protected array $mailables = [];

    /**
     * Create a new mail fake.
     */
    public function __construct(
        protected MailManager $manager
    ) {
    }

    /**
     * Assert if a mailable was sent based on a truth-test callback.
     */
    public function assertSent(Closure|string $mailable, null|array|callable|int|string $callback = null): void
    {
        [$mailable, $callback] = $this->prepareMailableAndCallback($mailable, $callback);

        if (is_numeric($callback)) {
            $this->assertSentTimes($mailable, $callback);
            return;
        }

        if (is_array($callback) || is_string($callback)) {
            foreach (Arr::wrap($callback) as $address) {
                $callback = fn ($mail) => $mail->hasTo($address);

                PHPUnit::assertTrue(
                    $this->sent($mailable, $callback)->count() > 0,
                    "The expected [{$mailable}] mailable was not sent to address [{$address}]."
                );
            }

            return;
        }

        PHPUnit::assertTrue(
            $this->sent($mailable, $callback)->count() > 0,
            "The expected [{$mailable}] mailable was not sent."
        );
    }

    /**
     * Assert if a mailable was sent a number of times.
     */
    protected function assertSentTimes(string $mailable, int $times = 1): void
    {
        $count = $this->sent($mailable)->count();

        PHPUnit::assertSame(
            $times,
            $count,
            "The expected [{$mailable}] mailable was sent {$count} times instead of {$times} times."
        );
    }

    /**
     * Determine if a mailable was not sent or queued to be sent based on a truth-test callback.
     */
    public function assertNotOutgoing(Closure|string $mailable, ?callable $callback = null): void
    {
        $this->assertNotSent($mailable, $callback);
    }

    /**
     * Determine if a mailable was not sent based on a truth-test callback.
     */
    public function assertNotSent(Closure|string $mailable, null|array|callable|string $callback = null): void
    {
        if (is_string($callback) || is_array($callback)) {
            foreach (Arr::wrap($callback) as $address) {
                $callback = fn ($mail) => $mail->hasTo($address);

                PHPUnit::assertCount(
                    0,
                    $this->sent($mailable, $callback),
                    "The unexpected [{$mailable}] mailable was sent to address [{$address}]."
                );
            }

            return;
        }

        [$mailable, $callback] = $this->prepareMailableAndCallback($mailable, $callback);

        PHPUnit::assertCount(
            0,
            $this->sent($mailable, $callback),
            "The unexpected [{$mailable}] mailable was sent."
        );
    }

    /**
     * Assert that no mailables were sent or queued to be sent.
     */
    public function assertNothingOutgoing(): void
    {
        $this->assertNothingSent();
    }

    /**
     * Assert that no mailables were sent.
     */
    public function assertNothingSent(): void
    {
        $mailableNames = Collection::make($this->mailables)->map(
            fn ($mailable) => get_class($mailable)
        )->join("\n- ");

        PHPUnit::assertEmpty($this->mailables, "The following mailables were sent unexpectedly:\n\n- {$mailableNames}\n");
    }

    /**
     * Assert the total number of mailables that were sent.
     */
    public function assertSentCount(int $count): void
    {
        $total = Collection::make($this->mailables)->count();

        PHPUnit::assertSame(
            $count,
            $total,
            "The total number of mailables sent was {$total} instead of {$count}."
        );
    }

    /**
     * Assert the total number of mailables that were sent or queued.
     */
    public function assertOutgoingCount(int $count): void
    {
        $total = Collection::make($this->mailables)
            ->count();

        PHPUnit::assertSame(
            $count,
            $total,
            "The total number of outgoing mailables was {$total} instead of {$count}."
        );
    }

    /**
     * Get all of the mailables matching a truth-test callback.
     */
    public function sent(Closure|string $mailable, ?callable $callback = null): Collection
    {
        [$mailable, $callback] = $this->prepareMailableAndCallback($mailable, $callback);

        if (! $this->hasSent($mailable)) {
            return Collection::make();
        }

        $callback = $callback ?: fn () => true;

        return $this->mailablesOf($mailable)->filter(fn ($mailable) => $callback($mailable));
    }

    /**
     * Determine if the given mailable has been sent.
     */
    public function hasSent(string $mailable): bool
    {
        return $this->mailablesOf($mailable)->count() > 0;
    }

    /**
     * Get all of the mailed mailables for a given type.
     */
    protected function mailablesOf(string $type): Collection
    {
        return Collection::make($this->mailables)->filter(fn ($mailable) => $mailable instanceof $type);
    }

    /**
     * Get a mailer instance by name.
     */
    public function mailer(?string $name = null): Mailer
    {
        $this->currentMailer = $name;

        return $this;
    }

    /**
     * Begin the process of mailing a mailable class instance.
     */
    public function to(mixed $users): PendingMail
    {
        return (new PendingMailFake($this))->to($users);
    }

    /**
     * Begin the process of mailing a mailable class instance.
     */
    public function cc(mixed $users): PendingMail
    {
        return (new PendingMailFake($this))->cc($users);
    }

    /**
     * Begin the process of mailing a mailable class instance.
     */
    public function bcc(mixed $users): PendingMail
    {
        return (new PendingMailFake($this))->bcc($users);
    }

    /**
     * Send a new message with only a raw text part.
     */
    public function raw(string $text, mixed $callback): ?SentMessage
    {
        return null;
    }

    /**
     * Send a new message using a view.
     */
    public function send(array|Mailable|string $view, array $data = [], null|Closure|string $callback = null): ?SentMessage
    {
        $this->sendMail($view);

        return null;
    }

    /**
     * Send a new message synchronously using a view.
     */
    public function sendNow(array|Mailable|string $mailable, array $data = [], null|Closure|string $callback = null): ?SentMessage
    {
        $this->sendMail($mailable);

        return null;
    }

    /**
     * Send a new message using a view.
     */
    protected function sendMail(array|Mailable|string $view): void
    {
        if (! $view instanceof Mailable) {
            return;
        }

        $view->mailer($this->currentMailer);

        $this->currentMailer = null;

        $this->mailables[] = $view;
    }

    /**
     * Infer mailable class using reflection if a typehinted closure is passed to assertion.
     */
    protected function prepareMailableAndCallback(Closure|string $mailable, ?callable $callback): array
    {
        if ($mailable instanceof Closure) {
            return [$this->firstClosureParameterType($mailable), $mailable];
        }

        return [$mailable, $callback];
    }

    /**
     * Forget all of the resolved mailer instances.
     */
    public function forgetMailers(): static
    {
        $this->currentMailer = null;

        return $this;
    }

    /**
     * Handle dynamic method calls to the mailer.
     */
    public function __call(string $method, array $parameters)
    {
        return $this->forwardCallTo($this->manager, $method, $parameters);
    }
}