<?php

namespace App\Modules\Usuarios\Services;

use App\Modules\Usuarios\Validation\BuyerProfileRules;
use Illuminate\Support\Facades\DB;

final class BuyerProfileService
{
    /** @return array<string,mixed>|null */
    public function find(int $userId): ?array
    {
        $profile = DB::table('tienda_perfiles_compradores')->where('user_id', $userId)->first();

        return $profile === null ? null : $this->present($profile);
    }

    public function isComplete(int $userId): bool
    {
        $profile = $this->find($userId);

        return $profile !== null
            && collect(['first_names', 'last_names', 'document_type', 'document_number'])
                ->every(static fn (string $key): bool => filled($profile[$key] ?? null));
    }

    /** Checkout-only readiness. Deliberately does not alter the registration completeness contract. */
    public function isCheckoutReady(int $userId): bool
    {
        $profile = $this->find($userId);

        return $profile !== null
            && collect(['line_1', 'city', 'state', 'country', 'phone_country_code', 'phone'])
                ->every(static fn (string $key): bool => filled($profile[$key] ?? null))
            && preg_match('/^[0-9]{7,15}$/D', (string) $profile['phone']) === 1
            && preg_match('/^\+[1-9][0-9]{0,3}$/D', (string) $profile['phone_country_code']) === 1
            && BuyerProfileRules::isIsoCountry((string) $profile['country']);
    }

    /** @param array<string,string|null> $input @return array<string,mixed> */
    public function save(int $userId, array $input): array
    {
        return DB::transaction(function () use ($userId, $input): array {
            $profile = DB::table('tienda_perfiles_compradores')
                ->where('user_id', $userId)->lockForUpdate()->first();

            $data = array_intersect_key($input, array_flip([
                'first_names','last_names','document_type','document_number',
                'phone','contact_email','line_1','line_2','city','state','country','phone_country_code',
            ]));

            if ($profile === null) {
                DB::table('tienda_perfiles_compradores')->insert(array_merge($data, [
                    'user_id' => $userId, 'created_at' => now(), 'updated_at' => now(),
                ]));
            } else {
                DB::table('tienda_perfiles_compradores')->where('id', $profile->id)
                    ->update(array_merge($data, ['updated_at' => now()]));
            }

            return $this->find($userId);
        });
    }

    /** @return array<string,mixed> */
    private function present(object $profile): array
    {
        return [
            'first_names' => $profile->first_names,
            'last_names' => $profile->last_names,
            'document_type' => $profile->document_type,
            'document_number' => $profile->document_number,
            'phone' => $profile->phone,
            'contact_email' => $profile->contact_email,
            'line_1' => $profile->line_1,
            'line_2' => $profile->line_2,
            'city' => $profile->city,
            'state' => $profile->state,
            'country' => $profile->country,
            'phone_country_code' => $profile->phone_country_code,
            'identity_verified' => false,
            'student_status_verified' => false,
        ];
    }
}
