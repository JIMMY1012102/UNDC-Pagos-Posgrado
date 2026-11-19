<?php

namespace App\Modules\Pagos\Enums;

enum TransactionStatus: string
{
    case PendingVerification = 'pending_verification';
}
