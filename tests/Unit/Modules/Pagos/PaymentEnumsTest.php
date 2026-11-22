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

    public function test_unknown_provider_states_are_uncertain_and_never_payable(): void
    {
        $this->assertSame(TransactionStatus::Uncertain, TransactionStatus::fromProviderState('ESTADO_FUTURO'));
        $this->assertFalse(TransactionStatus::fromProviderState('ESTADO_FUTURO')->isPayable());
    }

    public function test_provider_states_are_mapped_without_treating_pending_as_paid(): void
    {
        $this->assertSame(TransactionStatus::PendingVerification, TransactionStatus::fromProviderState('PENDIENTE'));
        $this->assertSame(TransactionStatus::Authorized, TransactionStatus::fromProviderState('AUTORIZADO'));
        $this->assertTrue(TransactionStatus::fromProviderState('LIQUIDADO')->isPayable());
        $this->assertFalse(TransactionStatus::fromProviderState('EXTORNADO')->isPayable());
    }

    public function test_incident_kinds_match_the_existing_schema_documentation(): void
    {
        $this->assertSame([
            'duplicate_charge',
            'uncertain',
            'mismatch',
            'late_payment',
        ], array_map(
            static fn (IncidentKind $kind): string => $kind->value,
            IncidentKind::cases(),
        ));
    }
}
