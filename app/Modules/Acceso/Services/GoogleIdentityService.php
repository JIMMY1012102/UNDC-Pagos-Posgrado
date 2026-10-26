<?php

namespace App\Modules\Acceso\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Two\User as GoogleUser;
use RuntimeException;

final class GoogleIdentityService
{
    public function resolve(GoogleUser $google): User
    {
        $subject = $google->getId();
        $email = $google->getEmail();
        $verified = $google->user['verified_email'] ?? $google->user['email_verified'] ?? false;

        if (! is_string($subject) || $subject === '' ||
            ! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL) ||
            $verified !== true) {
            throw new RuntimeException('GOOGLE_IDENTITY_UNVERIFIED');
        }

        return DB::transaction(function () use ($subject, $email, $google): User {
            $identity = DB::table('store_oauth_identities')
                ->where('provider', 'google')
                ->where('provider_subject', $subject)
                ->lockForUpdate()
                ->first();

            if ($identity !== null) {
                $user = User::query()->find($identity->user_id);

                if ($user === null || strcasecmp($user->email, $email) !== 0) {
                    throw new RuntimeException('GOOGLE_IDENTITY_CONFLICT');
                }

                return $user;
            }

            // Never link a new Google subject to an existing account by email
            // alone. Account linking requires its own authenticated workflow.
            if (User::query()->where('email', $email)->exists()) {
                throw new RuntimeException('GOOGLE_EMAIL_ALREADY_REGISTERED');
            }

            $user = User::query()->create([
                'name' => (string) ($google->getName() ?: $email),
                'email' => $email,
                'password' => Str::random(64),
            ]);

            DB::table('store_oauth_identities')->insert([
                'user_id' => $user->id,
                'provider' => 'google',
                'provider_subject' => $subject,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // No roles/permissions are granted at registration.
            // Google does not verify DNI, student enrollment or office access.
            return $user;
        });
    }
}
