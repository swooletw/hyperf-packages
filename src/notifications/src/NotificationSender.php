<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Notifications;

use Hyperf\Collection\Collection;
use Hyperf\Database\Model\Collection as ModelCollection;
use Hyperf\Database\Model\Model;
use Hyperf\Stringable\Str;
use Psr\EventDispatcher\EventDispatcherInterface;
use SwooleTW\Hyperf\Notifications\Events\NotificationSending;
use SwooleTW\Hyperf\Notifications\Events\NotificationSent;
use SwooleTW\Hyperf\Support\Traits\Localizable;
use SwooleTW\Hyperf\Translation\Contracts\HasLocalePreference;

use function Hyperf\Support\value;
use function Hyperf\Tappable\tap;

class NotificationSender
{
    use Localizable;

    /**
     * Create a new notification sender instance.
     */
    public function __construct(
        protected ChannelManager $manager,
        protected EventDispatcherInterface $events,
        protected ?string $locale = null
    ) {
    }

    /**
     * Send the given notification to the given notifiable entities.
     */
    public function send(mixed $notifiables, mixed $notification): void
    {
        $notifiables = $this->formatNotifiables($notifiables);

        // TODO: Support sending notifications through the queue in the future

        $this->sendNow($notifiables, $notification);
    }

    /**
     * Send the given notification immediately.
     */
    public function sendNow(mixed $notifiables, mixed $notification, ?array $channels = null): void
    {
        $notifiables = $this->formatNotifiables($notifiables);

        $original = clone $notification;

        foreach ($notifiables as $notifiable) {
            if (empty($viaChannels = $channels ?: $notification->via($notifiable))) {
                continue;
            }

            $this->withLocale($this->preferredLocale($notifiable, $notification), function () use ($viaChannels, $notifiable, $original) {
                $notificationId = Str::uuid()->toString();

                foreach ((array) $viaChannels as $channel) {
                    if (! ($notifiable instanceof AnonymousNotifiable && $channel === 'database')) {
                        $this->sendToNotifiable($notifiable, $notificationId, clone $original, $channel);
                    }
                }
            });
        }
    }

    /**
     * Get the notifiable's preferred locale for the notification.
     */
    protected function preferredLocale(mixed $notifiable, mixed $notification): ?string
    {
        return $notification->locale ?? $this->locale ?? value(function () use ($notifiable) {
            if ($notifiable instanceof HasLocalePreference) {
                return $notifiable->preferredLocale();
            }
        });
    }

    /**
     * Send the given notification to the given notifiable via a channel.
     */
    protected function sendToNotifiable(mixed $notifiable, string $id, mixed $notification, string $channel): void
    {
        if (! $notification->id) {
            $notification->id = $id;
        }

        if (! $this->shouldSendNotification($notifiable, $notification, $channel)) {
            return;
        }

        $response = $this->manager->driver($channel)->send($notifiable, $notification);

        $this->events->dispatch(
            new NotificationSent($notifiable, $notification, $channel, $response)
        );
    }

    /**
     * Determines if the notification can be sent.
     */
    protected function shouldSendNotification(mixed $notifiable, mixed $notification, string $channel): bool
    {
        if (method_exists($notification, 'shouldSend')
            && $notification->shouldSend($notifiable, $channel) === false
        ) {
            return false;
        }

        return tap(new NotificationSending($notifiable, $notification, $channel), function ($event) {
            $this->events?->dispatch($event);
        })->shouldSend();
    }

    /**
     * Format the notifiables into a Collection / array if necessary.
     */
    protected function formatNotifiables(mixed $notifiables): array|Collection
    {
        if (! $notifiables instanceof Collection && ! is_array($notifiables)) {
            return $notifiables instanceof Model
                ? new ModelCollection([$notifiables]) : [$notifiables];
        }

        return $notifiables;
    }
}
