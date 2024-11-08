<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Mail;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use Hyperf\ViewEngine\Contract\FactoryInterface as ViewFactory;
use Hyperf\ViewEngine\Contract\ViewInterface;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use SwooleTW\Hyperf\Auth\Contracts\FactoryContract;
use SwooleTW\Hyperf\Foundation\ApplicationContext;
use SwooleTW\Hyperf\Mail\Events\MessageSending;
use SwooleTW\Hyperf\Mail\Events\MessageSent;
use SwooleTW\Hyperf\Mail\Mailable;
use SwooleTW\Hyperf\Mail\Mailer;
use SwooleTW\Hyperf\Mail\MailManager;
use SwooleTW\Hyperf\Mail\Message;
use SwooleTW\Hyperf\Mail\Transport\ArrayTransport;
use SwooleTW\Hyperf\Support\HtmlString;

/**
 * @internal
 * @coversNothing
 */
class MailMailerTest extends TestCase
{
    protected ?Container $app = null;

    protected function setUp(): void
    {
        $this->app = $this->mockContainer();
    }

    protected function tearDown(): void
    {
        unset($_SERVER['__mailer.test']);

        m::close();
    }

    public function testMailerSendSendsMessageWithProperViewContent()
    {
        $view = $this->mockView();

        $mailer = new Mailer('array', $view, new ArrayTransport());

        $sentMessage = $mailer->send('foo', ['data'], function (Message $message) {
            $message->to('taylor@laravel-hyperf.com')->from('hello@laravel-hyperf.com');
        });

        $this->assertStringContainsString('rendered.view', $sentMessage->toString());
    }

    public function testMailerSendSendsMessageWithCcAndBccRecipients()
    {
        $view = $this->mockView();

        $mailer = new Mailer('array', $view, new ArrayTransport());

        $sentMessage = $mailer->send('foo', ['data'], function (Message $message) {
            $message->to('taylor@laravel-hyperf.com')
                ->cc('dries@laravel-hyperf.com')
                ->bcc('james@laravel-hyperf.com')
                ->from('hello@laravel-hyperf.com');
        });

        $recipients = collect($sentMessage->getEnvelope()->getRecipients())->map(function ($recipient) {
            return $recipient->getAddress();
        });

        $this->assertStringContainsString('rendered.view', $sentMessage->toString());
        $this->assertStringContainsString('dries@laravel-hyperf.com', $sentMessage->toString());
        $this->assertStringNotContainsString('james@laravel-hyperf.com', $sentMessage->toString());
        $this->assertTrue($recipients->contains('james@laravel-hyperf.com'));
    }

    public function testMailerSendSendsMessageWithProperViewContentUsingHtmlStrings()
    {
        $view = $this->mockView();

        $mailer = new Mailer('array', $view, new ArrayTransport());

        $sentMessage = $mailer->send(
            ['html' => new HtmlString('<p>Hello Laravel Hyperf</p>'), 'text' => new HtmlString('Hello World')],
            ['data'],
            function (Message $message) {
                $message->to('taylor@laravel-hyperf.com')->from('hello@laravel-hyperf.com');
            }
        );

        $this->assertStringNotContainsString('rendered.view', $sentMessage->toString());
        $this->assertStringContainsString('<p>Hello Laravel Hyperf</p>', $sentMessage->toString());
        $this->assertStringContainsString('Hello World', $sentMessage->toString());
    }

    public function testMailerSendSendsMessageWithProperViewContentUsingStringCallbacks()
    {
        $view = $this->mockView();

        $mailer = new Mailer('array', $view, new ArrayTransport());

        $sentMessage = $mailer->send(
            [
                'html' => function ($data) {
                    $this->assertInstanceOf(Message::class, $data['message']);

                    return new HtmlString('<p>Hello Laravel Hyperf</p>');
                },
                'text' => function ($data) {
                    $this->assertInstanceOf(Message::class, $data['message']);

                    return new HtmlString('Hello World');
                },
            ],
            [],
            function (Message $message) {
                $message->to('taylor@laravel-hyperf.com')->from('hello@laravel-hyperf.com');
            }
        );

        $this->assertStringNotContainsString('rendered.view', $sentMessage->toString());
        $this->assertStringContainsString('<p>Hello Laravel Hyperf</p>', $sentMessage->toString());
        $this->assertStringContainsString('Hello World', $sentMessage->toString());
    }

    public function testMailerSendSendsMessageWithProperViewContentUsingHtmlMethod()
    {
        $view = $this->mockView();

        $mailer = new Mailer('array', $view, new ArrayTransport());

        $sentMessage = $mailer->html('<p>Hello World</p>', function (Message $message) {
            $message->to('taylor@laravel-hyperf.com')->from('hello@laravel-hyperf.com');
        });

        $this->assertStringNotContainsString('rendered.view', $sentMessage->toString());
        $this->assertStringContainsString('<p>Hello World</p>', $sentMessage->toString());
    }

    public function testMailerSendSendsMessageWithProperPlainViewContent()
    {
        $viewInterface = m::mock(ViewInterface::class);
        $viewInterface->shouldReceive('render')
            ->once()
            ->andReturn('rendered.view');
        $viewInterface->shouldReceive('render')
            ->once()
            ->andReturn('rendered.plain');

        $view = m::mock(ViewFactory::class);
        $view->shouldReceive('make')->andReturn($viewInterface);

        $mailer = new Mailer('array', $view, new ArrayTransport());

        $sentMessage = $mailer->send(['foo', 'bar'], ['data'], function (Message $message) {
            $message->to('taylor@laravel-hyperf.com')->from('hello@laravel-hyperf.com');
        });

        $expected = <<<Text
        Content-Type: text/html; charset=utf-8\r
        Content-Transfer-Encoding: quoted-printable\r
        \r
        rendered.view
        Text;

        $this->assertStringContainsString($expected, $sentMessage->toString());

        $expected = <<<Text
        Content-Type: text/plain; charset=utf-8\r
        Content-Transfer-Encoding: quoted-printable\r
        \r
        rendered.plain
        Text;

        $this->assertStringContainsString($expected, $sentMessage->toString());
    }

    public function testMailerSendSendsMessageWithProperPlainViewContentWhenExplicit()
    {
        $viewInterface = m::mock(ViewInterface::class);
        $viewInterface->shouldReceive('render')
            ->once()
            ->andReturn('rendered.view');
        $viewInterface->shouldReceive('render')
            ->once()
            ->andReturn('rendered.plain');

        $view = m::mock(ViewFactory::class);
        $view->shouldReceive('make')->andReturn($viewInterface);

        $mailer = new Mailer('array', $view, new ArrayTransport());

        $sentMessage = $mailer->send(['html' => 'foo', 'text' => 'bar'], ['data'], function (Message $message) {
            $message->to('taylor@laravel-hyperf.com')->from('hello@laravel-hyperf.com');
        });

        $expected = <<<Text
        Content-Type: text/html; charset=utf-8\r
        Content-Transfer-Encoding: quoted-printable\r
        \r
        rendered.view
        Text;

        $this->assertStringContainsString($expected, $sentMessage->toString());

        $expected = <<<Text
        Content-Type: text/plain; charset=utf-8\r
        Content-Transfer-Encoding: quoted-printable\r
        \r
        rendered.plain
        Text;

        $this->assertStringContainsString($expected, $sentMessage->toString());
    }

    public function testToAllowsEmailAndName()
    {
        $view = $this->mockView();
        $mailer = new Mailer('array', $view, new ArrayTransport());

        $sentMessage = $mailer->to('taylor@laravel-hyperf.com', 'Taylor Otwell')->send(new TestMail());

        $recipients = $sentMessage->getEnvelope()->getRecipients();
        $this->assertCount(1, $recipients);
        $this->assertSame('taylor@laravel-hyperf.com', $recipients[0]->getAddress());
        $this->assertSame('Taylor Otwell', $recipients[0]->getName());
    }

    public function testGlobalFromIsRespectedOnAllMessages()
    {
        $view = $this->mockView();
        $mailer = new Mailer('array', $view, new ArrayTransport());
        $mailer->alwaysFrom('hello@laravel-hyperf.com');

        $sentMessage = $mailer->send('foo', ['data'], function (Message $message) {
            $message->to('taylor@laravel-hyperf.com');
        });

        $this->assertSame('taylor@laravel-hyperf.com', $sentMessage->getEnvelope()->getRecipients()[0]->getAddress());
        $this->assertSame('hello@laravel-hyperf.com', $sentMessage->getEnvelope()->getSender()->getAddress());
    }

    public function testGlobalReplyToIsRespectedOnAllMessages()
    {
        $view = $this->mockView();
        $mailer = new Mailer('array', $view, new ArrayTransport());
        $mailer->alwaysReplyTo('taylor@laravel-hyperf.com', 'Taylor Otwell');

        $sentMessage = $mailer->send('foo', ['data'], function (Message $message) {
            $message->to('dries@laravel-hyperf.com')->from('hello@laravel-hyperf.com');
        });

        $this->assertSame('dries@laravel-hyperf.com', $sentMessage->getEnvelope()->getRecipients()[0]->getAddress());
        $this->assertStringContainsString('Reply-To: Taylor Otwell <taylor@laravel-hyperf.com>', $sentMessage->toString());
    }

    public function testGlobalToIsRespectedOnAllMessages()
    {
        $view = $this->mockView();
        $mailer = new Mailer('array', $view, new ArrayTransport());
        $mailer->alwaysTo('taylor@laravel-hyperf.com', 'Taylor Otwell');

        $sentMessage = $mailer->send('foo', ['data'], function (Message $message) {
            $message->from('hello@laravel-hyperf.com');
            $message->to('nuno@laravel-hyperf.com');
            $message->cc('dries@laravel-hyperf.com');
            $message->bcc('james@laravel-hyperf.com');
        });

        $recipients = collect($sentMessage->getEnvelope()->getRecipients())->map(function ($recipient) {
            return $recipient->getAddress();
        });

        $this->assertSame('taylor@laravel-hyperf.com', $sentMessage->getEnvelope()->getRecipients()[0]->getAddress());
        $this->assertDoesNotMatchRegularExpression('/^To: nuno@laravel-hyperf.com/m', $sentMessage->toString());
        $this->assertDoesNotMatchRegularExpression('/^Cc: dries@laravel-hyperf.com/m', $sentMessage->toString());
        $this->assertMatchesRegularExpression('/^X-To: nuno@laravel-hyperf.com/m', $sentMessage->toString());
        $this->assertMatchesRegularExpression('/^X-Cc: dries@laravel-hyperf.com/m', $sentMessage->toString());
        $this->assertMatchesRegularExpression('/^X-Bcc: james@laravel-hyperf.com/m', $sentMessage->toString());
        $this->assertFalse($recipients->contains('nuno@laravel-hyperf.com'));
        $this->assertFalse($recipients->contains('dries@laravel-hyperf.com'));
        $this->assertFalse($recipients->contains('james@laravel-hyperf.com'));
    }

    public function testGlobalReturnPathIsRespectedOnAllMessages()
    {
        $view = $this->mockView();

        $mailer = new Mailer('array', $view, new ArrayTransport());
        $mailer->alwaysReturnPath('taylorotwell@gmail.com');

        $sentMessage = $mailer->send('foo', ['data'], function (Message $message) {
            $message->to('taylor@laravel-hyperf.com')->from('hello@laravel-hyperf.com');
        });

        $this->assertStringContainsString('Return-Path: <taylorotwell@gmail.com>', $sentMessage->toString());
    }

    public function testEventsAreDispatched()
    {
        $view = $this->mockView();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(MessageSending::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(MessageSent::class));

        $mailer = new Mailer('array', $view, new ArrayTransport(), $events);

        $mailer->send('foo', ['data'], function (Message $message) {
            $message->to('taylor@laravel-hyperf.com')->from('hello@laravel-hyperf.com');
        });
    }

    public function testMacroable()
    {
        Mailer::macro('foo', function () {
            return 'bar';
        });

        $mailer = new Mailer('array', m::mock(ViewFactory::class), new ArrayTransport());

        $this->assertSame(
            'bar',
            $mailer->foo()
        );
    }

    protected function mockContainer(): Container
    {
        $container = new Container(
            new DefinitionSource([
                ConfigInterface::class => fn () => m::mock(ConfigInterface::class),
                FactoryContract::class => MailManager::class,
                ViewFactory::class => ViewFactory::class,
                EventDispatcherInterface::class => fn () => m::mock(EventDispatcherInterface::class),
            ])
        );

        ApplicationContext::setContainer($container);

        return $container;
    }

    protected function mockView()
    {
        $viewInterface = m::mock(ViewInterface::class);
        $viewInterface->shouldReceive('render')
            ->andReturn('rendered.view');

        $view = m::mock(ViewFactory::class);
        $view->shouldReceive('make')->andReturn($viewInterface);

        return $view;
    }
}

class TestMail extends Mailable
{
    public function build()
    {
        return $this->view('view')
            ->from('hello@laravel-hyperf.com');
    }
}
