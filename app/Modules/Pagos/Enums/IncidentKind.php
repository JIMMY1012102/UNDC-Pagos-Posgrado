<?php

namespace App\Modules\Pagos\Enums;

enum IncidentKind: string
{
    case DuplicateCharge = 'duplicate_charge';
    case Uncertain = 'uncertain';
    case Mismatch = 'mismatch';
}
