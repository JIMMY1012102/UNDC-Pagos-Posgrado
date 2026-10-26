<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Una oficina puede incorporarse posteriormente sin nuevas tablas de tienda.
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->string('code', 32)->unique();
            $table->string('slug', 100)->unique();
            $table->string('name', 180);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('staff_roles', function (Blueprint $table) {
            $table->id();
            $table->string('code', 60)->unique();
            $table->string('name', 120);
            $table->timestamps();
        });

        // Alcance institucional explícito: usuario + rol + oficina.
        Schema::create('staff_role_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('staff_role_id')->constrained('staff_roles')->restrictOnDelete();
            $table->foreignId('office_id')->constrained('offices')->restrictOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'staff_role_id', 'office_id'], 'uq_staff_role_office');
        });

        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->restrictOnDelete();
            $table->string('first_names', 160)->nullable();
            $table->string('last_names', 160)->nullable();
            $table->string('document_type', 30)->nullable();
            $table->string('document_number', 32)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('contact_email', 254)->nullable();
            $table->timestamps();
            // El documento NO es único: evita afirmar identidad académica o civil.
            $table->index(['document_type', 'document_number'], 'idx_customer_document');
        });

        Schema::create('oauth_identities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('provider', 40);
            $table->string('provider_subject', 191);
            $table->timestamp('linked_at')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'provider_subject'], 'uq_oauth_subject');
        });

        Schema::create('beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_profile_id')->constrained('customer_profiles')->restrictOnDelete();
            $table->string('first_names', 160);
            $table->string('last_names', 160);
            $table->string('document_type', 30);
            $table->string('document_number', 32);
            $table->string('email', 254)->nullable();
            $table->string('phone', 30)->nullable();
            $table->timestamps();
            $table->index(['document_type', 'document_number'], 'idx_beneficiary_document');
        });

        Schema::create('concepts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained('offices')->restrictOnDelete();
            $table->string('code', 64);
            $table->string('slug', 150);
            $table->string('name', 220);
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->string('category', 100)->nullable();
            $table->boolean('requires_eligibility_check')->default(false);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->unique(['office_id', 'code'], 'uq_concept_office_code');
            $table->unique(['office_id', 'slug'], 'uq_concept_office_slug');
            $table->index(['office_id', 'is_active'], 'idx_concept_catalog');
        });

        // Historial de tarifas: la tienda tomará el precio vigente del servidor.
        Schema::create('concept_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('concept_id')->constrained('concepts')->restrictOnDelete();
            $table->decimal('amount', 14, 2);
            $table->char('currency', 3)->default('PEN');
            $table->timestamp('valid_from');
            $table->timestamp('valid_until')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approval_reference', 120)->nullable();
            $table->timestamps();
            $table->index(['concept_id', 'valid_from', 'valid_until'], 'idx_price_validity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('concept_prices');
        Schema::dropIfExists('concepts');
        Schema::dropIfExists('beneficiaries');
        Schema::dropIfExists('oauth_identities');
        Schema::dropIfExists('customer_profiles');
        Schema::dropIfExists('staff_role_assignments');
        Schema::dropIfExists('staff_roles');
        Schema::dropIfExists('offices');
    }
};
