<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            // Nunca persistir el identificador de sesión/carrito en texto claro.
            $table->char('token_hash', 64)->nullable()->unique();
            $table->char('currency', 3)->default('PEN');
            $table->string('status', 32)->default('active');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status'], 'idx_cart_owner_status');
        });

        Schema::create('store_cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('store_carts')->cascadeOnDelete();
            $table->foreignId('catalog_item_id')->constrained('store_catalog_items')->restrictOnDelete();
            $table->foreignId('beneficiary_id')->nullable()->constrained('store_beneficiaries')->restrictOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();
            $table->index(['cart_id', 'catalog_item_id'], 'idx_cart_concept');
            // Concepto repetido para distintos beneficiarios: agrupar en el servicio.
        });

        Schema::create('store_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 48)->unique();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('cart_id')->nullable()->constrained('store_carts')->nullOnDelete();
            $table->string('status', 32)->default('pending');
            $table->char('currency', 3)->default('PEN');
            $table->decimal('total_amount', 14, 2);
            // Instantánea inmutable a nivel de dominio: cambio de perfil no cambia el pedido.
            $table->string('buyer_first_names', 160);
            $table->string('buyer_last_names', 160);
            $table->string('buyer_document_type', 30);
            $table->string('buyer_document_number', 32);
            $table->string('buyer_email', 254);
            $table->string('buyer_phone', 30)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at'], 'idx_orders_customer_date');
            $table->index(['status', 'created_at'], 'idx_orders_status_date');
        });

        Schema::create('store_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('store_orders')->restrictOnDelete();
            $table->foreignId('catalog_item_id')->constrained('store_catalog_items')->restrictOnDelete();
            $table->integer('legacy_concept_id'); // tm_conceptos.id_concepto, snapshot
            $table->integer('legacy_office_id'); // tm_oficina.oficina_id, snapshot
            $table->foreignId('beneficiary_id')->nullable()->constrained('store_beneficiaries')->restrictOnDelete();
            $table->string('concept_code', 64);
            $table->string('concept_name', 220);
            $table->unsignedInteger('quantity');
            $table->decimal('unit_amount', 14, 2);
            $table->decimal('total_amount', 14, 2);
            $table->string('beneficiary_first_names', 160);
            $table->string('beneficiary_last_names', 160);
            $table->string('beneficiary_document_type', 30);
            $table->string('beneficiary_document_number', 32);
            $table->timestamps();
            $table->index(['legacy_office_id', 'order_id'], 'idx_items_office_order');
        });

        Schema::create('store_order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('store_orders')->restrictOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status', 32)->nullable();
            $table->string('to_status', 32);
            $table->string('reason', 255)->nullable();
            $table->json('context')->nullable();
            $table->timestamp('occurred_at');
            $table->index(['order_id', 'occurred_at'], 'idx_order_history');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_order_status_history');
        Schema::dropIfExists('store_order_items');
        Schema::dropIfExists('store_orders');
        Schema::dropIfExists('store_cart_items');
        Schema::dropIfExists('store_carts');
    }
};
