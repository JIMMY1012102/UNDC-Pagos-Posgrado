<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('store_orders')->restrictOnDelete();
            $table->string('channel', 32); // gateway, interbank, manual, future
            $table->string('provider', 40);
            $table->string('status', 32)->default('initiated');
            $table->string('idempotency_key', 100)->unique();
            $table->string('provider_session_reference', 150)->nullable();
            $table->decimal('requested_amount', 14, 2);
            $table->char('currency', 3)->default('PEN');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->json('non_sensitive_metadata')->nullable();
            $table->timestamps();
            $table->index(['order_id', 'status'], 'idx_attempts_order_status');
            $table->unique(['provider', 'provider_session_reference'], 'uq_provider_session');
        });

        // Registro de cobros observados, incluso si corresponden a un doble cobro.
        Schema::create('store_payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('store_orders')->restrictOnDelete();
            $table->foreignId('payment_attempt_id')->nullable()->constrained('store_payment_attempts')->restrictOnDelete();
            $table->string('provider', 40);
            $table->string('provider_transaction_reference', 150)->nullable();
            $table->string('status', 32)->default('pending_verification');
            $table->decimal('amount', 14, 2);
            $table->char('currency', 3)->default('PEN');
            $table->string('verification_source', 80)->nullable();
            $table->timestamp('observed_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->char('evidence_digest', 64)->nullable();
            $table->timestamps();
            $table->unique(['provider', 'provider_transaction_reference'], 'uq_provider_transaction');
            $table->index(['order_id', 'status'], 'idx_transactions_order_status');
        });

        // Garantía relacional: como máximo un pago aplicado a cada pedido.
        // Otros cobros se mantienen en payment_transactions como incidencias.
        Schema::create('store_order_payment_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('store_orders')->restrictOnDelete();
            $table->foreignId('payment_transaction_id')->unique()->constrained('store_payment_transactions')->restrictOnDelete();
            $table->timestamp('claimed_at');
        });

        Schema::create('store_payment_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 40);
            $table->string('event_key', 150);
            $table->foreignId('payment_attempt_id')->nullable()->constrained('store_payment_attempts')->nullOnDelete();
            $table->string('processing_status', 32)->default('received');
            $table->boolean('signature_valid')->default(false);
            $table->char('payload_digest', 64);
            $table->timestamp('received_at');
            $table->timestamp('processed_at')->nullable();
            $table->string('failure_reason', 255)->nullable();
            $table->timestamps();
            $table->unique(['provider', 'event_key'], 'uq_gateway_event');
            $table->index(['processing_status', 'received_at'], 'idx_events_processing');
            // No guardar tarjetas, tokens de pago ni payloads sensibles.
        });

        Schema::create('store_payment_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('store_orders')->restrictOnDelete();
            $table->foreignId('payment_transaction_id')->nullable()->constrained('store_payment_transactions')->restrictOnDelete();
            $table->string('kind', 60); // duplicate_charge, uncertain, mismatch...
            $table->string('status', 32)->default('open');
            $table->text('description')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['kind', 'status'], 'idx_incident_queue');
        });

        Schema::create('store_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_transaction_id')->constrained('store_payment_transactions')->restrictOnDelete();
            $table->string('provider_refund_reference', 150)->nullable();
            $table->decimal('amount', 14, 2);
            $table->char('currency', 3)->default('PEN');
            $table->string('status', 32)->default('requested');
            $table->string('reason', 255)->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('requested_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['payment_transaction_id', 'provider_refund_reference'], 'uq_refund_provider_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_refunds');
        Schema::dropIfExists('store_payment_incidents');
        Schema::dropIfExists('store_payment_webhook_events');
        Schema::dropIfExists('store_order_payment_claims');
        Schema::dropIfExists('store_payment_transactions');
        Schema::dropIfExists('store_payment_attempts');
    }
};
