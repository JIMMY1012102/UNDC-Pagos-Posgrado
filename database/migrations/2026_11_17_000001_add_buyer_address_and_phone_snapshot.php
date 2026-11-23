<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tienda_perfiles_compradores', function (Blueprint $table): void {
            $table->string('line_1', 255)->nullable();
            $table->string('line_2', 255)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('state', 120)->nullable();
            $table->char('country', 2)->default('PE');
            $table->string('phone_country_code', 5)->default('+51');
        });

        Schema::table('tienda_pedidos', function (Blueprint $table): void {
            $table->string('buyer_address_line_1', 255)->nullable();
            $table->string('buyer_address_line_2', 255)->nullable();
            $table->string('buyer_address_city', 120)->nullable();
            $table->string('buyer_address_state', 120)->nullable();
            $table->char('buyer_address_country', 2)->nullable();
            $table->string('buyer_phone_country_code', 5)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tienda_pedidos', function (Blueprint $table): void {
            $table->dropColumn([
                'buyer_address_line_1', 'buyer_address_line_2',
                'buyer_address_city', 'buyer_address_state',
                'buyer_address_country', 'buyer_phone_country_code',
            ]);
        });

        Schema::table('tienda_perfiles_compradores', function (Blueprint $table): void {
            $table->dropColumn([
                'line_1', 'line_2', 'city', 'state', 'country', 'phone_country_code',
            ]);
        });
    }
};
