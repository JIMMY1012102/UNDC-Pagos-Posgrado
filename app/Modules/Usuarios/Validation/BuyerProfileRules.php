<?php

namespace App\Modules\Usuarios\Validation;

use Illuminate\Validation\Rule;

final class BuyerProfileRules
{
    private const ISO_COUNTRIES = 'AD AE AF AG AI AL AM AO AQ AR AS AT AU AW AX AZ BA BB BD BE BF BG BH BI BJ BL BM BN BO BQ BR BS BT BV BW BY BZ CA CC CD CF CG CH CI CK CL CM CN CO CR CU CV CW CX CY CZ DE DJ DK DM DO DZ EC EE EG EH ER ES ET FI FJ FK FM FO FR GA GB GD GE GF GG GH GI GL GM GN GP GQ GR GS GT GU GW GY HK HM HN HR HT HU ID IE IL IM IN IO IQ IR IS IT JE JM JO JP KE KG KH KI KM KN KP KR KW KY KZ LA LB LC LI LK LR LS LT LU LV LY MA MC MD ME MF MG MH MK ML MM MN MO MP MQ MR MS MT MU MV MW MX MY MZ NA NC NE NF NG NI NL NO NP NR NU NZ OM PA PE PF PG PH PK PL PM PN PR PS PT PW PY QA RE RO RS RU RW SA SB SC SD SE SG SH SI SJ SK SL SM SN SO SR SS ST SV SX SY SZ TC TD TF TG TH TJ TK TL TM TN TO TR TT TV TW TZ UA UG UM US UY UZ VA VC VE VG VI VN VU WF WS YE YT ZA ZM ZW';

    public static function normalize(array $input): array
    {
        foreach (['first_names', 'last_names', 'document_type', 'document_number', 'phone', 'contact_email', 'line_1', 'line_2', 'city', 'state', 'country', 'phone_country_code'] as $field) {
            if (isset($input[$field]) && is_string($input[$field])) {
                $input[$field] = trim($input[$field]);
                if (in_array($field, ['document_type', 'document_number', 'country'], true)) {
                    $input[$field] = strtoupper($input[$field]);
                }
            }
        }

        return $input;
    }

    public static function rules(array $input): array
    {
        $format = ($input['document_type'] ?? null) === 'DNI'
            ? '/^[0-9]{8}$/D'
            : '/^[A-Z0-9]{6,20}$/D';

        return [
            'first_names' => ['required', 'string', 'max:160'],
            'last_names' => ['required', 'string', 'max:160'],
            'document_type' => ['required', 'string', Rule::in(['DNI', 'CE', 'PASAPORTE'])],
            'document_number' => ['required', 'string', 'regex:'.$format],
            'phone' => ['nullable', 'string', 'regex:/^[0-9]{7,15}$/D'],
            'phone_country_code' => ['nullable', 'string', 'regex:/^\+[1-9][0-9]{0,3}$/D', 'max:5'],
            'line_1' => ['nullable', 'string', 'max:255'],
            'line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'size:2', Rule::in(explode(' ', self::ISO_COUNTRIES))],
        ];
    }
}
