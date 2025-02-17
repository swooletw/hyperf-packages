<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Telescope\Watchers;

use Hyperf\Contract\ConfigInterface;
use Mockery as m;
use Psr\EventDispatcher\EventDispatcherInterface;
use SwooleTW\Hyperf\Mail\Events\MessageSent;
use SwooleTW\Hyperf\Mail\SentMessage;
use SwooleTW\Hyperf\Telescope\EntryType;
use SwooleTW\Hyperf\Telescope\Watchers\MailWatcher;
use SwooleTW\Hyperf\Tests\Telescope\FeatureTestCase;

/**
 * @internal
 * @coversNothing
 */
class MailWatcherTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->get(ConfigInterface::class)
            ->set('telescope.watchers', [
                MailWatcher::class => true,
            ]);

        $this->startTelescope();
    }

    public function testMailWatcherRegistersValidHtml()
    {
        $message = $this->mockSentMessage([
            'getBody' => '<!DOCTYPE html body',
            'getFrom' => ['from_address'],
            'getReplyTo' => ['reply_to_address'],
            'getTo' => ['to_address'],
            'getCc' => ['cc_address'],
            'getBcc' => ['bcc_address'],
            'getSubject' => 'subject',
            'toString' => 'raw',
        ]);

        $event = new MessageSent($message);

        $this->app->get(EventDispatcherInterface::class)
            ->dispatch($event);

        $entry = $this->loadTelescopeEntries()->first();

        $this->assertSame(EntryType::MAIL, $entry->type);
        $this->assertSame('<!DOCTYPE html body', $entry->content['html']);
        $this->assertSame('from_address', $entry->content['from'][0]);
        $this->assertSame('reply_to_address', $entry->content['replyTo'][0]);
        $this->assertSame('to_address', $entry->content['to'][0]);
        $this->assertSame('cc_address', $entry->content['cc'][0]);
        $this->assertSame('bcc_address', $entry->content['bcc'][0]);
        $this->assertSame('subject', $entry->content['subject']);
        $this->assertSame('raw', $entry->content['raw']);
    }

    protected function mockSentMessage(array $data): SentMessage
    {
        $originalMessage = m::mock('originalMessage');

        foreach ($data as $key => $value) {
            $originalMessage->shouldReceive($key)
                ->once()
                ->andReturn($value);
        }

        $message = m::mock(SentMessage::class);
        $message->shouldReceive('getOriginalMessage')
            ->once()
            ->andReturn($originalMessage);
        $message->shouldReceive('getBody')
            ->once()
            ->andReturn('body');

        return $message;
    }
}
