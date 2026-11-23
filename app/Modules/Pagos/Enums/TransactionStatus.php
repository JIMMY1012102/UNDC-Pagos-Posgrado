<?php

namespace App\Modules\Pagos\Enums;

enum TransactionStatus: string
{
    case PendingVerification = 'pending_verification';
    case Authorized = 'authorized';
    case Settled = 'settled';
    case Declined = 'declined';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case Reversed = 'reversed';
    case Invalid = 'invalid';
    case Uncertain = 'uncertain';

    public static function fromProviderState(string $state): self
    {
        return match (mb_strtoupper(trim($state))) {
            'REGISTRADO', 'PENDIENTE' => self::PendingVerification,
            'AUTORIZADO' => self::Authorized,
            'DENEGADO' => self::Declined,
            'EXPIRADO' => self::Expired,
            'CANCELADO' => self::Cancelled,
            'EXTORNADO' => self::Reversed,
            'LIQUIDADO' => self::Settled,
            'INVALIDO' => self::Invalid,
            default => self::Uncertain,
        };
    }

    public function isPayable(): bool
    {
        return in_array($this, [self::Authorized, self::Settled], true);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::Declined,
            self::Expired,
            self::Cancelled,
            self::Reversed,
            self::Invalid,
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
            [self::Declined, self::Expired, self::Cancelled, self::Reversed, self::Invalid],
        );
    }
}
