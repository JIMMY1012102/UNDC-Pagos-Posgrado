<?php

namespace App\Modules\Pagos\Enums;

enum AttemptStatus: string
{
    case Initiated = 'initiated';
    case PendingVerification = 'pending_verification';
    case Authorized = 'authorized';
    case Uncertain = 'uncertain';
    case Declined = 'declined';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case Failed = 'failed';

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::Declined,
            self::Expired,
            self::Cancelled,
            self::Failed,
        ], true);
    }

    public static function isTerminalValue(string $value): bool
    {
        return self::tryFrom($value)?->isTerminal() ?? false;
    }

    /** @return list<string> */
    public static function terminalValues(): array
    {
        return array_map(
            static fn (self $status): string => $status->value,
            [self::Declined, self::Expired, self::Cancelled, self::Failed],
        );
    }
}
