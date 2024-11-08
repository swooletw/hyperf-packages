<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Mail;

use PHPUnit\Framework\TestCase;
use SwooleTW\Hyperf\Mail\Attachment;
use SwooleTW\Hyperf\Mail\Contracts\Attachable;
use SwooleTW\Hyperf\Mail\Mailable;

/**
 * @internal
 * @coversNothing
 */
class AttachableTest extends TestCase
{
    public function testItCanHaveMacroConstructors()
    {
        Attachment::macro('fromInvoice', function ($name) {
            return Attachment::fromData(fn () => 'pdf content', $name);
        });
        $mailable = new Mailable();

        $mailable->attach(new class implements Attachable {
            public function toMailAttachment(): Attachment
            {
                return Attachment::fromInvoice('foo')
                    ->as('bar')
                    ->withMime('image/jpeg');
            }
        });

        $this->assertSame([
            'data' => 'pdf content',
            'name' => 'bar',
            'options' => [
                'mime' => 'image/jpeg',
            ],
        ], $mailable->rawAttachments[0]);
    }

    public function testItCanUtiliseExistingApisOnNonMailBasedResourcesWithPath()
    {
        Attachment::macro('size', function () {
            return 99;
        });
        $notification = new class {
            public $pathArgs;

            public function withPathAttachment()
            {
                $this->pathArgs = func_get_args();
            }
        };
        $attachable = new class implements Attachable {
            public function toMailAttachment(): Attachment
            {
                return Attachment::fromPath('foo.jpg')
                    ->as('bar')
                    ->withMime('text/css');
            }
        };

        $attachable->toMailAttachment()->attachWith(
            fn ($path, $attachment) => $notification->withPathAttachment($path, $attachment->as, $attachment->mime, $attachment->size()),
            fn () => null
        );

        $this->assertSame([
            'foo.jpg',
            'bar',
            'text/css',
            99,
        ], $notification->pathArgs);
    }

    public function testItCanUtiliseExistingApisOnNonMailBasedResourcesWithArgs()
    {
        Attachment::macro('size', function () {
            return 99;
        });
        $notification = new class {
            public $pathArgs;

            public $dataArgs;

            public function withDataAttachment()
            {
                $this->dataArgs = func_get_args();
            }
        };
        $attachable = new class implements Attachable {
            public function toMailAttachment(): Attachment
            {
                return Attachment::fromData(fn () => 'expected attachment body', 'bar')
                    ->withMime('text/css');
            }
        };

        $attachable->toMailAttachment()->attachWith(
            fn () => null,
            fn ($data, $attachment) => $notification->withDataAttachment($data(), $attachment->as, $attachment->mime, $attachment->size()),
        );

        $this->assertSame([
            'expected attachment body',
            'bar',
            'text/css',
            99,
        ], $notification->dataArgs);
    }
}
