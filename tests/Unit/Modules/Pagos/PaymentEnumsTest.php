<?php

namespace Tests\Unit\Modules\Pagos;

use App\Modules\Pagos\Enums\AttemptStatus;
use App\Modules\Pagos\Enums\IncidentKind;
use App\Modules\Pagos\Enums\TransactionStatus;
use PHPUnit\Framework\TestCase;

class PaymentEnumsTest extends TestCase
{
    public function test_attempt_status_matches_the_existing_database_default(): void
    {
        $this->assertSame('initiated', AttemptStatus::Initiated->value);
    }

    public function test_transaction_status_matches_the_existing_database_default(): void
    {
        $this->assertSame('pending_verification', TransactionStatus::PendingVerification->value);
    }

    public function test_incident_kinds_match_the_existing_schema_documentation(): void
    {
        $this->assertSame([
            'duplicate_charge',
            'uncertain',
            'mismatch',
        ], array_map(
            static fn (IncidentKind $kind): string => $kind->value,
            IncidentKind::cases(),
        ));
    }
}
