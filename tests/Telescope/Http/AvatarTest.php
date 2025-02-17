<?php

declare(strict_types=1);

namespace SwooleTW\Hyperf\Tests\Telescope\Http;

use Hyperf\Contract\ConfigInterface;
use Psr\Log\LoggerInterface;
use SwooleTW\Hyperf\Auth\Contracts\Authenticatable;
use SwooleTW\Hyperf\Database\Eloquent\Model;
use SwooleTW\Hyperf\Telescope\Http\Middleware\Authorize;
use SwooleTW\Hyperf\Telescope\Telescope;
use SwooleTW\Hyperf\Telescope\Watchers\LogWatcher;
use SwooleTW\Hyperf\Tests\Telescope\FeatureTestCase;

/**
 * @internal
 * @coversNothing
 */
class AvatarTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(Authorize::class);

        $this->app->get(ConfigInterface::class)
            ->set('telescope.watchers', [
                LogWatcher::class => true,
            ]);
        $this->app->get(ConfigInterface::class)
            ->set('logging.default', 'null');

        $this->startTelescope();

        $this->loadServiceProviders();
    }

    public function testItCanRegisterCustomAvatarPath()
    {
        $user = null;

        Telescope::withoutRecording(function () use (&$user) {
            $this->createUsersTable();

            $user = UserEloquent::create([
                'id' => 1,
                'name' => 'Telescope',
                'email' => 'telescope@laravel.com',
                'password' => 'secret',
            ]);
        });

        Telescope::avatar(function ($id) {
            return "/images/{$id}.jpg";
        });

        $this->actingAs($user);

        $this->app->get(LoggerInterface::class)
            ->error('Avatar path will be generated.', [
                'exception' => 'Some error message',
            ]);

        $entry = $this->loadTelescopeEntries()->first();

        $this->get("/telescope/telescope-api/logs/{$entry->uuid}")
            ->assertOk()
            ->assertJson([
                'entry' => [
                    'content' => [
                        'user' => [
                            'avatar' => '/images/1.jpg',
                        ],
                    ],
                ],
            ]);
    }
}

class UserEloquent extends Model implements Authenticatable
{
    protected ?string $table = 'users';

    protected array $guarded = [];

    public function getAuthIdentifierName(): string
    {
        return $this->email;
    }

    public function getAuthIdentifier(): string
    {
        return (string) $this->id;
    }

    public function getAuthPassword(): string
    {
        return $this->password;
    }
}
