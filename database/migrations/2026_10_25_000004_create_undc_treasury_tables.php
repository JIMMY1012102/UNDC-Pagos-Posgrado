<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Un lote puede representar conciliación Pay-me o archivo bancario de Interbank.
        Schema::create('store_reconciliation_batches', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 40);
            $table->string('channel', 32);
            $table->string('external_batch_reference', 150)->nullable();
            $table->char('source_file_digest', 64)->nullable();
            $table->string('status', 32)->default('imported');
            $table->date('period_from')->nullable();
            $table->date('period_until')->nullable();
            $table->decimal('reported_total', 14, 2)->nullable();
            $table->char('currency', 3)->default('PEN');
            $table->integer('legacy_importer_id')->nullable(); // tm_usuario.usu_id
            $table->timestamp('reconciled_at')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'external_batch_reference'], 'uq_recon_provider_batch');
            $table->unique(['provider', 'source_file_digest'], 'uq_recon_provider_file');
            $table->index(['provider', 'status'], 'idx_recon_queue');
        });

        Schema::create('store_reconciliation_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reconciliation_batch_id')->constrained('store_reconciliation_batches')->restrictOnDelete();
            $table->unsignedInteger('row_number');
            $table->integer('legacy_result_id')->nullable(); // tm_resultado.i_resultado
            $table->integer('legacy_recaudacion_id')->nullable(); // tm_recaudacion.id_recaudacion
            $table->foreignId('payment_transaction_id')->nullable()->constrained('store_payment_transactions')->restrictOnDelete();
            $table->string('external_payment_reference', 160)->nullable();
            $table->string('debtor_code', 100)->nullable();
            $table->string('concept_code', 100)->nullable();
            $table->string('installment_reference', 80)->nullable();
            $table->decimal('gross_amount', 14, 2);
            $table->decimal('fee_amount', 14, 2)->default(0);
            $table->decimal('net_amount', 14, 2);
            $table->char('currency', 3)->default('PEN');
            $table->string('status', 32)->default('unmatched');
            $table->timestamp('bank_operation_at')->nullable();
            $table->timestamp('matched_at')->nullable();
            $table->timestamps();
            $table->unique(['reconciliation_batch_id', 'row_number'], 'uq_recon_batch_row');
            $table->index(['external_payment_reference'], 'idx_recon_reference');
            $table->index('legacy_result_id', 'idx_recon_legacy_result');
            $table->index('legacy_recaudacion_id', 'idx_recon_legacy_recaudacion');
            $table->index(['status', 'bank_operation_at'], 'idx_recon_status_date');
        });

        // Puente sin FK cruzadas ni escrituras sobre las tablas antiguas de SISPAGOS.
        Schema::create('store_external_system_links', function (Blueprint $table) {
            $table->id();
            $table->string('system_code', 50); // sispagos, interbank, other
            $table->string('external_entity', 80); // tm_resultado, etc.
            $table->string('external_record_key', 190);
            $table->integer('legacy_record_id')->nullable(); // exact signed legacy PK, when available
            $table->foreignId('order_id')->nullable()->constrained('store_orders')->restrictOnDelete();
            $table->foreignId('payment_transaction_id')->nullable()->constrained('store_payment_transactions')->restrictOnDelete();
            $table->string('sync_status', 32)->default('pending');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->unique(['system_code', 'external_entity', 'external_record_key'], 'uq_external_link');
            $table->index(['order_id', 'system_code'], 'idx_external_order');
        });

        // Validación de Tesorería separada de pago reportado y de conciliación bancaria.
        Schema::create('store_payment_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('store_orders')->restrictOnDelete();
            $table->foreignId('payment_transaction_id')->constrained('store_payment_transactions')->restrictOnDelete();
            $table->integer('legacy_reviewer_id')->nullable(); // tm_usuario.usu_id
            $table->string('status', 32)->default('pending');
            $table->string('evidence_reference', 150)->nullable();
            $table->string('decision_reason', 255)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at'], 'idx_validation_queue');
        });

        Schema::create('store_validation_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_validation_id')->unique()->constrained('store_payment_validations')->restrictOnDelete();
            $table->foreignId('order_id')->constrained('store_orders')->restrictOnDelete();
            $table->string('certificate_code', 80)->unique();
            $table->char('verification_token_hash', 64)->unique();
            $table->string('status', 32)->default('valid');
            $table->integer('legacy_issuer_id')->nullable(); // tm_usuario.usu_id
            $table->timestamp('issued_at');
            $table->integer('legacy_revoker_id')->nullable(); // tm_usuario.usu_id
            $table->timestamp('revoked_at')->nullable();
            $table->string('revocation_reason', 255)->nullable();
            $table->timestamps();
            $table->index(['order_id', 'status'], 'idx_certificate_order');
        });

        Schema::create('store_audit_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_buyer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('legacy_staff_id')->nullable(); // tm_usuario.usu_id
            $table->string('event_name', 120);
            $table->string('subject_type', 80);
            $table->unsignedBigInteger('subject_id');
            $table->string('correlation_id', 100)->nullable();
            $table->json('safe_context')->nullable();
            $table->timestamp('occurred_at');
            $table->index(['subject_type', 'subject_id', 'occurred_at'], 'idx_audit_subject');
            $table->index(['event_name', 'occurred_at'], 'idx_audit_event');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_audit_events');
        Schema::dropIfExists('store_validation_certificates');
        Schema::dropIfExists('store_payment_validations');
        Schema::dropIfExists('store_external_system_links');
        Schema::dropIfExists('store_reconciliation_entries');
        Schema::dropIfExists('store_reconciliation_batches');
    }
};
