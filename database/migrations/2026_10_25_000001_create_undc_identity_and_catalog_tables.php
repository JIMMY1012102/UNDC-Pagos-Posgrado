<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Canonical source: tm_oficina. Only store opt-in storefront settings here.
        Schema::create('store_office_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('legacy_office_id')->unique(); // tm_oficina.oficina_id (signed INT)
            $table->string('slug', 120)->unique();
            $table->boolean('catalog_enabled')->default(false);
            $table->string('display_name', 160)->nullable();
            $table->timestamps();
        });

        // Canonical concept, code, accounting fields and base cost remain in tm_conceptos.
        // Explicit mapping is needed: centro_costo is NOT an established foreign key.
        Schema::create('store_catalog_items', function (Blueprint $table) {
            $table->id();
            $table->integer('legacy_concept_id'); // tm_conceptos.id_concepto (signed INT)
            $table->integer('legacy_office_id'); // tm_oficina.oficina_id (signed INT)
            $table->string('slug', 150);
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->string('category', 100)->nullable();
            $table->boolean('requires_eligibility_check')->default(false);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->unique(['legacy_office_id', 'legacy_concept_id'], 'uq_store_catalog_legacy_pair');
            $table->unique(['legacy_office_id', 'slug'], 'uq_store_catalog_slug');
            $table->index(['legacy_concept_id'], 'idx_store_catalog_legacy_concept');
        });

        // Future price versions; currently tm_conceptos.costo remains the legacy source.
        // A service must reconcile published prices with approved treasury rates.
        Schema::create('store_catalog_price_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_item_id')->constrained('store_catalog_items')->restrictOnDelete();
            $table->decimal('amount', 14, 2);
            $table->char('currency', 3)->default('PEN');
            $table->timestamp('valid_from');
            $table->timestamp('valid_until')->nullable();
            $table->string('approval_reference', 150)->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
            $table->index(['catalog_item_id', 'valid_from', 'valid_until'], 'idx_store_price_validity');
        });

        // Laravel users are external buyers; tm_usuario is reserved for SISPAGOS staff.
        Schema::create('store_customer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->restrictOnDelete();
            $table->string('first_names', 160)->nullable();
            $table->string('last_names', 160)->nullable();
            $table->string('document_type', 30)->nullable();
            $table->string('document_number', 32)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('contact_email', 254)->nullable();
            $table->timestamps();
            $table->index(['document_type', 'document_number'], 'idx_store_customer_document');
        });

        Schema::create('store_oauth_identities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('provider', 40);
            $table->string('provider_subject', 191);
            $table->timestamps();
            $table->unique(['provider', 'provider_subject'], 'uq_store_oauth_identity');
        });

        Schema::create('store_beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_profile_id')->constrained('store_customer_profiles')->restrictOnDelete();
            $table->string('first_names', 160);
            $table->string('last_names', 160);
            $table->string('document_type', 30);
            $table->string('document_number', 32);
            $table->string('email', 254)->nullable();
            $table->string('phone', 30)->nullable();
            $table->timestamps();
            $table->index(['document_type', 'document_number'], 'idx_store_beneficiary_document');
        });

        // Deliberately NO foreign keys to tm_* until the actual treasury schema is
        // approved and installed. No ALTER/CREATE/DROP of legacy tables here.
    }

    public function down(): void
    {
        Schema::dropIfExists('store_beneficiaries');
        Schema::dropIfExists('store_oauth_identities');
        Schema::dropIfExists('store_customer_profiles');
        Schema::dropIfExists('store_catalog_price_versions');
        Schema::dropIfExists('store_catalog_items');
        Schema::dropIfExists('store_office_settings');
    }
};
