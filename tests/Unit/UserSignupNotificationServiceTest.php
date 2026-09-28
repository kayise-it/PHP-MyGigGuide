<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\UserSignupNotificationService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UserSignupNotificationServiceTest extends TestCase
{
    public function test_stays_quiet_when_evolution_is_not_configured(): void
    {
        config([
            'services.evolution.url' => '',
            'services.evolution.api_key' => '',
            'services.evolution.admin_number' => '',
        ]);

        Http::fake();

        $this->notifier()->notifyAdmin($this->sampleUser(), 'website');

        Http::assertNothingSent();
    }

    public function test_sends_signup_details_to_the_admin_number(): void
    {
        config([
            'services.evolution.url' => 'http://evolution.test',
            'services.evolution.instance' => 'default',
            'services.evolution.api_key' => 'secret',
            'services.evolution.admin_number' => '27746609752',
        ]);

        Http::fake();

        $user = $this->sampleUser();
        $user->password = 'secret-password';

        $this->notifier()->notifyAdmin($user, 'website');

        Http::assertSent(function ($request) {
            $text = $request['text'];

            return $request->url() === 'http://evolution.test/message/sendText/default'
                && $request->hasHeader('apikey', 'secret')
                && $request['number'] === '27746609752'
                && str_contains($text, 'New user: Test Person')
                && str_contains($text, 'Username: testperson')
                && str_contains($text, 'Email: test@example.com')
                && str_contains($text, 'Signed up: website')
                && ! str_contains($text, 'secret-password');
        });
    }

    public function test_whatsapp_failure_does_not_throw(): void
    {
        config([
            'services.evolution.url' => 'http://evolution.test',
            'services.evolution.instance' => 'default',
            'services.evolution.api_key' => 'secret',
            'services.evolution.admin_number' => '27746609752',
        ]);

        Http::fake(function () {
            throw new ConnectionException('Evolution is down');
        });

        $this->notifier()->notifyAdmin($this->sampleUser(), 'app');

        $this->assertTrue(true);
    }

    private function notifier(): UserSignupNotificationService
    {
        return new UserSignupNotificationService;
    }

    private function sampleUser(): User
    {
        return new User([
            'name' => 'Test Person',
            'username' => 'testperson',
            'email' => 'test@example.com',
        ]);
    }
}
