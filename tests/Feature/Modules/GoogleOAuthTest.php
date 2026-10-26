<?php

namespace Tests\Feature\Modules;

use App\Models\User;
use App\Modules\Acceso\Services\GoogleIdentityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Two\User as GoogleUser;
use RuntimeException;
use Tests\TestCase;

class GoogleOAuthTest extends TestCase
{
    use RefreshDatabase;

    private function googleUser(string $subject, string $email): GoogleUser
    {
        $user = new GoogleUser();
        $user->id = $subject;
        $user->email = $email;
        $user->name = 'Comprador de prueba';
        $user->user = ['verified_email' => true];

        return $user;
    }

    public function test_google_login_is_disabled_by_default(): void
    {
        $this->get('/auth/google/redirect')->assertNotFound();
        $this->get('/auth/google/callback')->assertNotFound();
        $this->assertSame(0, User::query()->count());
    }

    public function test_first_google_login_creates_unprivileged_buyer_and_provider_mapping(): void
    {
        $user = app(GoogleIdentityService::class)->resolve(
            $this->googleUser('subject-101', 'nuevo@example.test')
        );

        $this->assertDatabaseHas('store_oauth_identities', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_subject' => 'subject-101',
        ]);
        $this->assertSame([], $user->getRoleNames()->all());
        $this->assertNull($user->email_verified_at);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_repeat_subject_returns_same_buyer(): void
    {
        $service = app(GoogleIdentityService::class);
        $user = $service->resolve($this->googleUser('subject-123', 'buyer@example.test'));
        $again = $service->resolve($this->googleUser('subject-123', 'buyer@example.test'));

        $this->assertSame($user->id, $again->id);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_existing_email_cannot_be_silently_linked_to_new_google_subject(): void
    {
        User::factory()->create(['email' => 'existing@example.test']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('GOOGLE_EMAIL_ALREADY_REGISTERED');
        app(GoogleIdentityService::class)->resolve(
            $this->googleUser('other-subject', 'existing@example.test')
        );
    }

    public function test_unverified_provider_email_is_refused(): void
    {
        $google = $this->googleUser('subject-555', 'unverified@example.test');
        $google->user = ['verified_email' => false];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('GOOGLE_IDENTITY_UNVERIFIED');
        app(GoogleIdentityService::class)->resolve($google);
    }
}
