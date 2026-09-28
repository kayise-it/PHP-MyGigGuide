<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laratrust\Models\Role;
use Tests\TestCase;

class UserSignupWhatsAppTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
    }

    public function test_app_register_sends_one_whatsapp_and_still_succeeds_if_evolution_errors(): void
    {
        $this->configureEvolution();
        Http::fake([
            'evolution.test/*' => Http::response('nope', 500),
        ]);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Mobile Tester',
            'email' => 'mobile.tester@example.com',
            'password' => 'password123',
            'username' => 'mobiletester',
        ]);

        $response->assertCreated();
        $this->assertSame(['Signed up: app'], $this->signupLines());
        $this->assertStringNotContainsString('password123', $this->signupLinesText());
    }

    public function test_app_register_stays_quiet_when_evolution_is_not_configured(): void
    {
        config([
            'services.evolution.url' => '',
            'services.evolution.api_key' => '',
            'services.evolution.admin_number' => '',
        ]);
        Http::fake();

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Quiet Tester',
            'email' => 'quiet.tester@example.com',
            'password' => 'password123',
        ]);

        $response->assertCreated();
        Http::assertNothingSent();
    }

    public function test_website_register_form_labels_website(): void
    {
        Mail::fake();
        $this->configureEvolution();
        Http::fake();

        $this->post('/register', [
            'name' => 'New Fan',
            'username' => 'newfan',
            'email' => 'newfan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => '1',
        ])->assertRedirect(route('dashboard'));

        $this->assertSame(['Signed up: website'], $this->signupLines());
    }

    public function test_website_popup_register_labels_website_popup(): void
    {
        Mail::fake();
        $this->configureEvolution();
        Http::fake();

        $this->post('/register', [
            'name' => 'Popup Fan',
            'email' => 'popup@example.com',
            'password' => 'password123',
            'continue' => '/events',
        ])->assertRedirect('/events');

        $this->assertSame(['Signed up: website popup'], $this->signupLines());
    }

    public function test_new_google_sign_in_sends_one_google_whatsapp(): void
    {
        config(['services.firebase.web_api_key' => 'test-firebase-key']);
        $this->configureEvolution();

        Http::fake([
            'identitytoolkit.googleapis.com/*' => Http::response([
                'users' => [[
                    'localId' => 'firebase-new-uid',
                    'email' => 'google@example.com',
                    'displayName' => 'Google User',
                ]],
            ]),
            'evolution.test/*' => Http::response(['ok' => true], 200),
        ]);

        $this->post('/auth/firebase', [
            'id_token' => 'valid.jwt.token',
        ])->assertRedirect(route('dashboard'));

        $this->assertSame(['Signed up: Google'], $this->signupLines());
        $this->assertNotNull(User::query()->where('email', 'google@example.com')->first());
    }

    public function test_existing_google_sign_in_does_not_send(): void
    {
        config(['services.firebase.web_api_key' => 'test-firebase-key']);
        $this->configureEvolution();

        User::factory()->create([
            'email' => 'already@example.com',
            'firebase_uid' => 'firebase-existing-uid',
            'email_verified_at' => now(),
        ]);

        Http::fake([
            'identitytoolkit.googleapis.com/*' => Http::response([
                'users' => [[
                    'localId' => 'firebase-existing-uid',
                    'email' => 'already@example.com',
                    'displayName' => 'Already Here',
                ]],
            ]),
        ]);

        $this->post('/auth/firebase', [
            'id_token' => 'valid.jwt.token',
        ])->assertRedirect(route('dashboard'));

        $this->assertSame([], $this->signupLines());
    }

    public function test_admin_created_user_sends_admin_whatsapp(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $this->configureEvolution();
        Http::fake();

        $admin = User::factory()->create();
        $admin->addRole('admin');

        $roleId = Role::query()->where('name', 'user')->value('id');

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Admin Made',
            'username' => 'adminmade',
            'email' => 'adminmade@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => $roleId,
        ])->assertRedirect(route('admin.users.index'));

        $this->assertSame(['Signed up: admin'], $this->signupLines());
        $this->assertStringNotContainsString('password123', $this->signupLinesText());
    }

    private function configureEvolution(): void
    {
        config([
            'services.evolution.url' => 'http://evolution.test',
            'services.evolution.instance' => 'default',
            'services.evolution.api_key' => 'secret',
            'services.evolution.admin_number' => '27746609752',
        ]);
    }

    /**
     * @return list<string>
     */
    private function signupLines(): array
    {
        return collect(Http::recorded())
            ->filter(fn ($pair) => str_contains($pair[0]->url(), '/message/sendText/'))
            ->map(function ($pair) {
                $this->assertSame('27746609752', $pair[0]['number']);

                return collect(preg_split("/\r\n|\n|\r/", (string) $pair[0]['text']))
                    ->first(fn ($line) => str_starts_with($line, 'Signed up: '));
            })
            ->filter()
            ->values()
            ->all();
    }

    private function signupLinesText(): string
    {
        return collect(Http::recorded())
            ->filter(fn ($pair) => str_contains($pair[0]->url(), '/message/sendText/'))
            ->map(fn ($pair) => (string) $pair[0]['text'])
            ->implode("\n");
    }
}
